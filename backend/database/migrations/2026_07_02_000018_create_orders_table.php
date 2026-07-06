<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->constrained('order_contacts');
            $table->foreignId('delivery_id')->constrained('delivery_services');
            $table->foreignId('delivery_branch_id')->nullable()->constrained('delivery_branches')->nullOnDelete();
            $table->foreignId('payment_id')->constrained('payments');
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->decimal('paid_amount', 10, 2)->unsigned()->nullable();
            $table->string('txid')->unique();
            $table->string('tracking_number')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('tracking_number');
        });
    }

    public function down(): void {
        Schema::dropIfExists('orders');
    }
};
