<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('products_categories');
            $table->foreignId('author_id')->constrained('products_authors');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating_avg', 2, 1)->unsigned()->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->tinyInteger('bestseller')->unsigned()->default(0);
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('rating_avg');
            $table->index('bestseller');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};
