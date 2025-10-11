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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->comment('民國年');
            $table->integer('month')->comment('月份 (1-12)');
            $table->boolean('is_confirmed')->default(false)->comment('是否已確認');
            $table->timestamp('confirmed_at')->nullable()->comment('確認時間');
            $table->unsignedBigInteger('confirmed_by')->nullable()->comment('確認者 (admin_id)');
            $table->timestamps();

            $table->unique(['year', 'month']);
            $table->index('is_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
