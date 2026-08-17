<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('news_categories');
            $table->string('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->json('image')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('news_authors')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('published_at');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('news_posts');
    }
};
