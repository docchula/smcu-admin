<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('year');
            $table->smallInteger('number');
            $table->string('name');
            $table->string('advisor')->nullable();
            $table->string('type', 20)->nullable();
            $table->string('recurrence', 20)->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('background')->nullable();
            $table->text('aims')->nullable();
            $table->text('outcomes')->nullable();
            $table->text('objectives')->nullable();
            $table->text('expense')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('approval_document_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
