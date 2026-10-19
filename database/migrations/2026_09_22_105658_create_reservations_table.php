<?php


use App\Models\Inventory\InventoryItem;
use App\Models\Order\OrderItem;
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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(InventoryItem::class)->constrained()->onDelete('cascade');

            $table->unsignedInteger('quantity');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('status');
            $table->foreignIdFor(OrderItem::class)->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
