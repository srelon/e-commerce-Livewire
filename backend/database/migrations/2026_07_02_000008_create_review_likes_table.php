<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('opp_type')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['review_id', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('review_likes');
    }
};
