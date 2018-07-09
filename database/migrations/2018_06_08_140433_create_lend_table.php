<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLendTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lend', function (Blueprint $table) {
            $table->increments('id');
            $table->string('classroom', 30)->default('none');
            $table->string('personName', 30)->default('none');
            $table->string('personId', 30)->default('none');
            $table->string('phone', 30)->default('none');
            $table->string('org', 30)->default('none');
            $table->string('reason', 100)->default('none');
            $table->integer('year')->default(-1);
            $table->integer('month')->default(-1);
            $table->integer('date')->default(-1);
            $table->integer('start_hour')->default(-1);
            $table->integer('start_minute')->default(-1);
            $table->integer('end_hour')->default(-1);
            $table->integer('end_minute')->default(-1);
            $table->integer('pass')->default(0); //-1 unpass, 0 checking, 1 passed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lend');
    }
}
