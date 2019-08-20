<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApproverLoanApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approver_loan_application', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_application_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedTinyInteger('loan_application_status_id')->index();
            $table->timestamps();

            $table->primary(['loan_application_id', 'user_id'], 'approver_loan_application_pk');

            $table->foreign('loan_application_id', 'fk_approver_loan_app_loan_id')->references('id')
                ->on('loan_applications')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id', 'fk_approver_loan_app_user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('loan_application_status_id', 'fk_approver_loan_app_status_id')->references('id')
                ->on('loan_application_statuses')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approver_loan_application');
    }
}
