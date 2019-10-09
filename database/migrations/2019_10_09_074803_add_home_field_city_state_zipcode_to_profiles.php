<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHomeFieldCityStateZipcodeToProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('home_field_city')->after('home_field_address');
            $table->string('home_field_state')->after('home_field_city');
            $table->string('home_field_zipcode')->after('home_field_state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
             $table->dropColumn('home_field_city');
             $table->dropColumn('home_field_state');
             $table->dropColumn('home_field_zipcode');
        });
    }
}
