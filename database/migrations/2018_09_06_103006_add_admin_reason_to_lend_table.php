<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminReasonToLendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 添加管理员驳回理由（或者同意理由）
        Schema::table('lend', function (Blueprint $table) {
            $table->string('admin_reason', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 回滚添加字段
        Schema::table('lend', function (Blueprint $table) {
            $table->dropColumn('admin_reason');
        });
    }
}
