<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('route')->nullable();
            $table->integer('parent_id')->default(-1);
            $table->string('type')->default('route');
            $table->json('params')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('location')->default('header');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
