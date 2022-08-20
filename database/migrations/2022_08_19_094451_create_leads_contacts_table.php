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
        Schema::create('leads_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leadId');
            $table->unsignedBigInteger('contactId');

            $table->foreign('leadId')->references('amoId')->on('leads')->onDelete('cascade');
            $table->foreign('contactId')->references('amoId')->on('contacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_contacts');
    }
};
