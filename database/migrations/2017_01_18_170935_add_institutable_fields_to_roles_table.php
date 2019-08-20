<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstitutableFieldsToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_unique');
            $table->string('institutable_type')->nullable();
            $table->unsignedBigInteger('institutable_id')->nullable();

            $table->unique(['name', 'institutable_id', 'institutable_type'], 'roles_institution_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            // If table already contains conflicting data, this migration would fail
            $table->unique('name', 'roles_name_unique');
            $table->dropUnique('roles_institution_name_unique');
            $table->dropColumn(['institutable_type', 'institutable_id']);
        });
    }
}
