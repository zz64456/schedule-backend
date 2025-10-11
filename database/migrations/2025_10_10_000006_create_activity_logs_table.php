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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_type', 50)->comment('操作類型: employee_selected, schedule_updated, admin_login, schedule_confirmed, schedule_exported, admin_logout');
            $table->enum('user_type', ['guest', 'admin'])->default('guest')->comment('使用者類型');
            $table->unsignedBigInteger('user_id')->nullable()->comment('使用者ID (admin_id or null)');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('關聯員工ID');
            $table->unsignedBigInteger('schedule_id')->nullable()->comment('關聯班表ID');
            $table->string('ip_address', 45)->nullable()->comment('IP位址');
            $table->text('user_agent')->nullable()->comment('瀏覽器資訊');
            $table->json('details')->nullable()->comment('詳細資訊 (JSON格式)');
            $table->timestamps();

            $table->index('action_type');
            $table->index('user_type');
            $table->index('created_at');
            $table->index(['employee_id', 'created_at']);
            $table->index(['schedule_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
