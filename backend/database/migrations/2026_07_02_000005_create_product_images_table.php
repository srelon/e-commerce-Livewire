<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('image');
            $table->unsignedInteger('sort_order')->default(999);
            $table->timestamps();
            $table->softDeletes();

            $table->index('sort_order');
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_images');
    }
};
