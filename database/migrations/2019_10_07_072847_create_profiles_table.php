<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('image');
            $table->string('dob');
            $table->string('gender');
            $table->string('cob');
            $table->string('cop');
            $table->string('height');
            $table->string('weight');
            $table->string('team_name');
            $table->string('club_address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('pitch_type');
            $table->string('capacity');
            $table->string('website');
            $table->string('instagram');
            $table->string('twitter');
            $table->string('coach_name');
            $table->integer('is_profile_complete')->default(false);
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
        Schema::dropIfExists('profiles');
    }
}
