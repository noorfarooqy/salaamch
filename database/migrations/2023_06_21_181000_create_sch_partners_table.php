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
        Schema::create('sch_partners', function (Blueprint $table) {
            $table->id();
            $table->string('partner_name');
            $table->string('partner_country');
            $table->string('partner_city');
            $table->string('partner_email');
            $table->string('partner_telephone');
            $table->string('partner_contact_name');
            $table->foreignId('created_by')->nullable()->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_sch_transactions');
    }
};
