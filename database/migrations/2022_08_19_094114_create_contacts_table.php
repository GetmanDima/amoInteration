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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amoId')->unique();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->unsignedBigInteger('responsibleUserId');
            $table->unsignedBigInteger('groupId');
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy');
            $table->unsignedBigInteger('createdAt');
            $table->unsignedBigInteger('updatedAt');
            $table->unsignedBigInteger('closestTaskAt')->nullable();
            $table->unsignedBigInteger('accountId');
            $table->boolean('isMain')->nullable();

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
        Schema::dropIfExists('contacts');
    }
};
