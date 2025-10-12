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
        Schema::table('schedule_records', function (Blueprint $table) {
            $table->string('leave_type', 20)->nullable()->after('is_off')->comment('假別類型: personal=事假, sick=病假, null=一般休假');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_records', function (Blueprint $table) {
            $table->dropColumn('leave_type');
        });
    }
};
