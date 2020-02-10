<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tryout_id')->unsigned();
            $table->foreign('tryout_id')->references('id')->on('tryouts')->onDelete('cascade');
            $table->unsignedBigInteger('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('team_id')->unsigned();
            $table->foreign('team_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('leman_fees');
            $table->string('tryout_fees');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction');
    }
}
