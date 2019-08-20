<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestedLoanDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requested_loan_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('loan_application_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('request');
            $table->mediumText('response')->nullable();
            $table->timestamps();

            $table->foreign('loan_application_id', 'fk_req_docs_loan_app_id')->references('id')->on('loan_applications')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id', 'fk_req_docs_user_id')->references('id')->on('users')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requested_loan_documents');
    }
}
