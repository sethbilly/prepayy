<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIdentificationCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identification_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('number');
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();

            $table->foreign('user_id', 'fk_id_cards_user_id')->references('id')->on('users')
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
        Schema::dropIfExists('identification_cards');
    }
}
