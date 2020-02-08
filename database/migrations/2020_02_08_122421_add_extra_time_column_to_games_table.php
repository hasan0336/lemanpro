<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraTimeColumnToGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
           $table->string('ext_first_hlf_start')->after('game_type');
           $table->string('ext_first_hlf_end')->after('ext_first_hlf_start');
           $table->string('ext_second_hlf_start')->after('ext_first_hlf_end');
           $table->string('ext_second_hlf_end')->after('ext_second_hlf_start');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            //$table->string('ext_first_hlf_start')->after('game_type');
           $table->string('ext_first_hlf_end');
           $table->string('ext_second_hlf_start');
           $table->string('ext_second_hlf_end');
        });
    }
}
