<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ProductExport implements FromArray
{
    protected $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function array(): array
    {
        return $this->products;
    }
}
