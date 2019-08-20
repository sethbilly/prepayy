<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSsnitDobAndCountryFieldsToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('dob')->nullable();
            $table->string('ssnit')->nullable();
            $table->unsignedInteger('country_id')->nullable()->index();

            $table->foreign('country_id', 'fk_users_country_id')->references('id')->on('countries')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_country_id');
            $table->dropColumn(['dob', 'ssnit', 'country_id']);
        });
    }
}
