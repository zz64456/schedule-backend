<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade')->comment('部門ID');
            $table->string('name', 50)->comment('員工姓名');
            $table->string('color', 7)->default('#3B82F6')->comment('代表色 (HEX格式)');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->integer('sort_order')->default(0)->comment('排序順序');
            $table->timestamps();

            $table->index(['department_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
