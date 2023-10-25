<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();

            $table->smallInteger('year')->index();
            $table->unsignedInteger('department_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('position');
            $table->string('position_en')->nullable();
            $table->foreignId('supervisor')->nullable();
            $table->mediumInteger('sequence');
            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }
};
