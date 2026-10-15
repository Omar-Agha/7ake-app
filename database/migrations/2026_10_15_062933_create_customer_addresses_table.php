<?php

use App\Models\Customer;
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
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->string('address_name');
            $table->string('country');
            $table->string('city');
            $table->text('address_description');
            $table->boolean('is_default')->default(false);
   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
