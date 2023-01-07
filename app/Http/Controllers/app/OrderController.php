<?php

namespace App\Http\Controllers\app;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\BusinessUser;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('auth.type:customer')->only(['cancel', 'store']);
        $this->middleware('auth.type:user')->only(['accept', 'reject', 'deliver', 'complete']);
        $this->middleware('role:business_owner')->only(['accept', 'reject', 'deliver', 'complete']);
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        if($user::class == Customer::class)
            $orders = Order::with('items')->where('customer_id', $user->id)->get();
        elseif($user::class == User::class)
            $orders = Order::with('items')->where('business_id', $request->business->id)->get();
        return OrderResource::collection($orders);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'mobile'             => ['required', 'regex:/^\+?[0-9]{5,15}$/'],
            'address'            => ['required', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*'            => ['required_array_keys:product_id,quantity'],
            'items.*.product_id' => ['exists:products,id'],
            'items.*.quantity'   => ['numeric', 'min:0'],
        ]);
        $total_price = 0;
        $order = new Order([
            'customer_id' => $user->id,
            'business_id' => $request->business->id,
            'status'      => 'pending',
            'mobile'      => $request->mobile,
            'address'     => $request->address,
            'total_price' => $total_price,
            'currency_id' => 1
        ]);
        $order->save();
        
        foreach($request->items as $item){
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $product = Product::find($product_id);
            $price = $product->price;
            $currency_id = $product->currency_id;
            
            $order->total_price += $price * $quantity;
            $order->currency_id = $product->currency_id;

            $order->items()->attach([ $product_id => [
                'quantity'    => $quantity,
                'price'       => $price,
                'currency_id' => $currency_id,
            ]]);
        }

        $order->save();
        return response()->json(new OrderResource($order), 200);
    }

    public function show(Request $request, Order $order)
    {
        $user = Auth::user();
        if($user::class == Customer::class)
        {
            if($order->customer_id != $user->id)
                throw new AuthorizationException();
        }

        elseif($user::class == User::class)
        {
            $is_owner = BusinessUser::where('user_id', $user->id)
                                    ->where('business_id', $order->business_id)                                
                                    ->exists();
            if(!$is_owner)
                return new AuthorizationException();
            
            if($order->business_id != $request->business->id)
                throw new AuthorizationException();
        }
        return response()->json(new OrderResource($order), 200);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'mobile'             => ['required', 'regex:/^\+?[0-9]{5,15}$/'],
            'address'            => ['required', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*'            => ['required_array_keys:product_id,quantity'],
            'items.*.product_id' => ['exists:products,id'],
            'items.*.quantity'   => ['numeric', 'min:0'],
        ]);

        $user = Auth::user();
        if($user::class == Customer::class)
        {
            if($order->customer_id != $user->id)
                throw new AuthorizationException(); 

            if($order->status != 'pending' && $order->status != 'accepted')
                throw new BadRequestException();

            if($order->status == 'accepted'){
                $order_data = [
                    'mobile'  => $request->mobile,
                    'address' => $request->address,
                ];

                $order->update($order_data);
                return response()->json(new OrderResource($order), 200);
            }    
        }

        elseif($user::class == User::class)
        {
            $is_owner = BusinessUser::where('user_id', $user->id)
                                    ->where('business_id', $order->business_id)                                
                                    ->exists();
            if(!$is_owner)
                return new AuthorizationException();

            if($order->business_id != $request->business->id)
                throw new AuthorizationException();

            if($order->status != 'accepted' && $order->status != 'pending')
                throw new BadRequestException();                   
        }

        $order_data = [
            'mobile'      => $request->mobile,
            'address'     => $request->address,
        ];

        $order->update($order_data);
        $order->total_price = 0;

        $items = [];
        foreach($request->items as $item){
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];

            $product = Product::find($product_id);
            $price = $product->price;
            $currency_id = $product->currency_id;

            $order->total_price += $price * $quantity;
            $order->currency_id = $product->currency_id;

            $items[$product_id] = [
                'quantity'    => $quantity,
                'price'       => $price,
                'currency_id' => $currency_id,
            ];
        }
        $order->items()->sync($items);
        $order->save();
        return response()->json(new OrderResource($order), 200);
    }

    public function cancel(Order $order)
    {
        $user = Auth::user();
        if($order->customer_id != $user->id)
            throw new AuthorizationException();
        
       if($order->status != 'pending')
            throw new BadRequestException();

        $order->status = 'canceled';
        $order->save();
        return response()->json(new OrderResource($order), 200);
    }
   
    public function accept(Request $request,Order $order)
    {
        $user = Auth::user();
        $is_owner = BusinessUser::where('user_id', $user->id)
                                ->where('business_id', $order->business_id)                                
                                ->exists();
        if(!$is_owner)
            return new AuthorizationException();
        
        if($order->business_id != $request->business->id)
            throw new AuthorizationException();
        
       if($order->status != 'pending')
            throw new BadRequestException();

        $order->status = 'accepted';
        $order->save();
        return response()->json(new OrderResource($order), 200);
    }

    public function reject(Request $request,Order $order)
    {
        $user = Auth::user();
        $is_owner = BusinessUser::where('user_id', $user->id)
                                ->where('business_id', $order->business_id)                                
                                ->exists();
        if(!$is_owner)
            return new AuthorizationException();
        
        if($order->business_id != $request->business->id)
            throw new AuthorizationException();
        
       if($order->status != 'pending')
            throw new BadRequestException();

        $order->status = 'rejected';
        $order->save();
        return response()->json(new OrderResource($order), 200);
    }

    public function deliver(Request $request,Order $order)
    {
        $user = Auth::user();
        $is_owner = BusinessUser::where('user_id', $user->id)
                                ->where('business_id', $order->business_id)                                
                                ->exists();
        if(!$is_owner)
            return new AuthorizationException();
        
        if($order->business_id != $request->business->id)
            throw new AuthorizationException();
        
       if($order->status != 'accepted')
            throw new BadRequestException();

        $order->status = 'delivery';
        $order->save();
        return response()->json(new OrderResource($order), 200);
    }

    public function complete(Request $request,Order $order)
    {
        $user = Auth::user();
        $is_owner = BusinessUser::where('user_id', $user->id)
                                ->where('business_id', $order->business_id)                                
                                ->exists();
        if(!$is_owner)
            return new AuthorizationException();
        
        if($order->business_id != $request->business->id)
            throw new AuthorizationException();
        
       if($order->status != 'delivery')
            throw new BadRequestException();
        
        $order->status = 'delivered';
        $order->save();
        return response()->json(new OrderResource($order), 200);
    }
}

