<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->nullable()->unique();
            $table->unsignedBigInteger('guarantor_id')->index()->nullable();
            $table->unsignedBigInteger('employer_id')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('loan_product_id')->index();
            $table->unsignedBigInteger('identification_card_id')->index()->nullable();
            $table->unsignedTinyInteger('loan_application_status_id')->index()->nullable();
            $table->timestamps();

            $table->foreign('guarantor_id', 'fk_loan_app_guarantor_id')->references('id')->on('guarantors')
                ->onUpdate('cascade');
            $table->foreign('employer_id', 'fk_loan_app_employer_id')->references('id')->on('employers')
                ->onUpdate('cascade');
            $table->foreign('user_id', 'fk_loan_app_user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('identification_card_id', 'fk_loan_app_id_card_id')->references('id')
                ->on('identification_cards')->onUpdate('cascade');
            $table->foreign('loan_application_status_id', 'fk_loan_app_status_id')->references('id')
                ->on('loan_application_statuses')->onUpdate('cascade');
            $table->foreign('loan_product_id', 'fk_loan_app_product_id')->references('id')
                ->on('loan_products')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_applications');
    }
}
