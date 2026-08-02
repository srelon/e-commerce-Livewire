<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('delivery_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('delivery_services')->cascadeOnDelete();
            $table->string('city');
            $table->string('branch');
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['delivery_id', 'city', 'branch']);
            $table->index('city');
            $table->index('status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('delivery_branches');
    }
};
