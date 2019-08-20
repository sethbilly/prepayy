<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filename');
            $table->string('mime');
            $table->string('original_filename')->nullable();
            // The bucket or directory into which the file was uploaded
            $table->string('bucket')->nullable();
            // Owner of the file
            $table->unsignedBigInteger('fileable_id')->nullable();
            $table->string('fileable_type', 100)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['fileable_id', 'fileable_type'], 'file_entries_fileable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_entries');
    }
}
