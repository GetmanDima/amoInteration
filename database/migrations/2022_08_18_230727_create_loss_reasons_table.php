<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loss_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amoId')->unique();
            $table->string('name');
            $table->integer('sort');
            $table->bigInteger('createdAt');
            $table->bigInteger('updatedAt');

            $table->unsignedBigInteger('amoUserId');

            $table->foreign('amoUserId')->references('id')->on('amo_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loss_reasons');
    }
};
