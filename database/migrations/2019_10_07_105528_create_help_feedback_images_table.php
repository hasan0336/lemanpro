<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpFeedbackImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_feedback_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('help_feedback_id')->unsigned();
            $table->foreign('help_feedback_id')->references('id')->on('help_feedbacks')->onDelete('cascade');
            $table->string('help_feedback_image');
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
        Schema::dropIfExists('help_feedback_images');
    }
}
