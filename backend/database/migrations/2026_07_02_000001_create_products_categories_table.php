<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('icon')->nullable();
            $table->json('image')->nullable();
            $table->unsignedInteger('sort_order')->default(999);
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('sort_order');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('products_categories');
    }
};
