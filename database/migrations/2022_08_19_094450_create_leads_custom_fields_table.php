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
        Schema::create('leads_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leadId');
            $table->unsignedBigInteger('customFieldId');
            $table->json('values')->nullable();

            $table->foreign('leadId')->references('amoId')->on('leads')->onDelete('cascade');
            $table->foreign('customFieldId')->references('amoId')->on('custom_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_custom_fields');
    }
};
