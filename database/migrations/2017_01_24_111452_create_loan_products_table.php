<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('interest_per_year', 5, 2)->nullable();
            $table->unsignedBigInteger('financial_institution_id')->index();
            $table->timestamps();

            $table->foreign('financial_institution_id', 'fk_loan_products_institution_id')->references('id')
                ->on('financial_institutions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_products');
    }
}
