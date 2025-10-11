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
        Schema::create('schedule_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade')->comment('班表ID');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade')->comment('員工ID');
            $table->integer('day')->comment('日期 (1-31)');
            $table->boolean('is_off')->default(false)->comment('是否休假');
            $table->timestamps();

            $table->unique(['schedule_id', 'employee_id', 'day']);
            $table->index(['schedule_id', 'day']);
            $table->index(['employee_id', 'schedule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_records');
    }
};
