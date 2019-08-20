<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->nullable()->unique();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('othernames')->nullable();
            $table->string('email')->unique();
            $table->string('contact_number')->nullable()->unique();
            $table->string('password')->nullable();
            $table->unsignedTinyInteger('is_app_owner')->default(0);
            $table->unsignedTinyInteger('is_account_owner')->default(0);
            $table->rememberToken();
            $table->unsignedBigInteger('institutable_id')->nullable();
            $table->string('institutable_type', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
