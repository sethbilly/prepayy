<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployerUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employer_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('employer_id')->index();
            $table->string('contract_type');
            $table->string('position');
            $table->string('department')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_emp_user_user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('employer_id', 'fk_emp_user_employer_id')->references('id')->on('employers')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employer_user');
    }
}
