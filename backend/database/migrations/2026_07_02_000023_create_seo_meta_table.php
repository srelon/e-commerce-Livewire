<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('record_id');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['type', 'record_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('seo_meta');
    }
};
