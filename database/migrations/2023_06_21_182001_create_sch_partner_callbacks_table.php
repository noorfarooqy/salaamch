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
        Schema::create('sch_partner_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->references('id')->on('sch_transactions')->onDelete('cascade');
            $table->string('account_number')->nullable();
            $table->string('branch_code')->nullable();
            $table->boolean('request_sent')->default(false);
            $table->boolean('response_received')->default(false);
            $table->string('bank_reference')->nullable();
            $table->string('system_reference')->nullable();
            $table->longText('request_response')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->foreignId('initiated_by')->nullable()->references('id')->on('users');
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
