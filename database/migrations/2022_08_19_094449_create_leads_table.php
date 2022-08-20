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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amoId')->unique();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('responsibleUserId');
            $table->unsignedBigInteger('groupId');
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy');
            $table->unsignedBigInteger('createdAt');
            $table->unsignedBigInteger('updatedAt');
            $table->unsignedBigInteger('accountId');
            $table->unsignedBigInteger('pipelineId');
            $table->unsignedBigInteger('statusId');
            $table->unsignedBigInteger('closedAt')->nullable();
            $table->unsignedBigInteger('closestTaskAt')->nullable();
            $table->integer('price');
            $table->unsignedBigInteger('lossReasonId')->nullable();
            $table->boolean('isDeleted');
            $table->unsignedBigInteger('sourceId')->nullable();
            $table->string('sourceExternalId')->nullable();
            $table->integer('score')->nullable();
            $table->integer('isPriceModifiedByRobot')->nullable();
            $table->integer('companyId')->nullable();
            $table->string('visitorUid')->nullable();

            $table->unsignedBigInteger('amoUserId');

            $table->foreign('amoUserId')->references('id')->on('amo_users')->onDelete('cascade');

            $table->foreign('lossReasonId')->references('amoId')->on('loss_reasons')->onDelete('cascade');
            $table->foreign('companyId')->references('amoId')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
};
