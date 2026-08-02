<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('order_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('email');
            $table->foreignId('delivery_id')->nullable()->constrained('delivery_services')->nullOnDelete();
            $table->foreignId('delivery_branch_id')->nullable()->constrained('delivery_branches')->nullOnDelete();
            $table->timestamp('last_ordered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('user_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('order_contacts');
    }
};
