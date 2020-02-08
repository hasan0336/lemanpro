<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPlayerExtraTimeColumnToMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->string('player_ext_hlf_start')->after('player_end_time');
            $table->string('player_ext_hlf_end')->after('player_ext_hlf_start');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->string('player_ext_hlf_start');
            $table->string('player_ext_hlf_end');
        });
    }
}
