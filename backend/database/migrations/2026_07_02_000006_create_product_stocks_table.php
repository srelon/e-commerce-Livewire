<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('before_price', 10, 2)->unsigned()->nullable();
            $table->decimal('real_price', 10, 2)->unsigned()->default(0);
            $table->unsignedInteger('sort_order')->default(999);
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('price');
            $table->index('sort_order');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
