<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_info', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('content');
            $table->text('icon')->nullable();
            $table->string('key')->unique();
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->unsignedInteger('sort_order')->default(999);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_info');
    }
};
