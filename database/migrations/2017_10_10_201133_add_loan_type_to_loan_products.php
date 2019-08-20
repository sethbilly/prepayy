<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoanTypeToLoanProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->unsignedSmallInteger('loan_type_id')->index()->nullable();
            $table->foreign('loan_type_id', 'fk_loan_prod_type_id')->references('id')
                ->on('loan_types')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropForeign('fk_loan_prod_type_id');
            $table->dropColumn('loan_type_id');
        });
    }
}
