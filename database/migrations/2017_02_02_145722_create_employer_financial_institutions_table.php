<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployerFinancialInstitutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employer_financial_institution', function (Blueprint $table) {
            $table->unsignedBigInteger('financial_institution_id')->index();
            $table->unsignedBigInteger('employer_id')->index();
            $table->timestamps();

            $table->primary(['financial_institution_id', 'employer_id'], 'emp_fin_inst_pk');

            $table->foreign('financial_institution_id', 'fk_emp_fin_inst_institution_id')->references('id')
                ->on('financial_institutions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('employer_id', 'fk_emp_fin_inst_employer_id')->references('id')
                ->on('employers')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employer_financial_institution');
    }
}
