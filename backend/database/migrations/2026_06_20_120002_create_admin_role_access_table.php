<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admin_role_access', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->foreignId('access_id')->constrained('admin_accesses')->cascadeOnDelete();
            $table->tinyInteger('type')->unsigned()->comment('1=view, 2=edit');
            $table->primary(['role_id', 'access_id', 'type']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('admin_role_access');
    }
};
