<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuarantorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guarantors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('relationship');
            $table->string('contact_number', 20);
            $table->decimal('years_known', 5, 2);
            $table->string('employer');
            $table->string('position');
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();

            $table->foreign('user_id', 'fk_guarantors_user_id')->references('id')
                ->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guarantors');
    }
}
