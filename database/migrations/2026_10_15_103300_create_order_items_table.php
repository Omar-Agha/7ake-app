<?php

use App\Models\Catalog\ProductVariant;
use App\Models\Order\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Order::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(ProductVariant::class)->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 15, 2);
            $table->decimal('total', 15, 2)->default(0);
            $table->string('sku')->nullable();
            $table->string('currency', 3)->default('USD');
            
   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
