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
        Schema::create('sch_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->references('id')->on('sch_partners');
            $table->string('src_transaction_id')->unique();
            $table->string('src_trn_head_id');
            $table->string('bank_transaction_id')->nullable();
            $table->string('bank_cbs_reference')->nullable();
            $table->string('sender_id');
            $table->string('sender_name');
            $table->string('description');
            $table->unsignedDouble('local_amount');
            $table->unsignedDouble('amount_in_usd');
            $table->unsignedDouble('charge_amount')->default(0);
            $table->unsignedDouble('current_balance')->default(0);
            $table->unsignedInteger('status_code')->nullable();
            $table->string('status_message')->nullable();
            $table->string('beneficiary_account_number')->nullable();;
            $table->string('bank_code')->nullable();
            $table->string('bank_account_pan')->nullable();
            $table->string('bank_account_title')->nullable();
            $table->boolean('is_success')->default(0);
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
