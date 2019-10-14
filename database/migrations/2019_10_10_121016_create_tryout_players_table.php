<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTryoutPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tryout_players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('tryout_id')->unsigned();
            $table->foreign('tryout_id')->references('id')->on('tryouts')->onDelete('cascade');
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
        Schema::dropIfExists('tryout_players');
    }
}
