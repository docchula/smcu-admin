<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->mediumInteger('year')->index();
            $table->mediumInteger('number');
            $table->mediumInteger('number_to')->nullable();
            $table->string('title');
            $table->string('recipient')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->string('attachment_path')->nullable();

            $table->index(['year', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
