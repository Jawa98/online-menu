<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['subscribe','renew','change_plan']);
            $table->enum('duration', ['month', 'quarter', 'year'])->nullable();
            $table->float('price', 10, 2);
            $table->foreignId('business_id')->constrained('businesses')
            ->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('plans')
            ->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
