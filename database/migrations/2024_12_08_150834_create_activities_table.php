<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('organization');
            $table->decimal('duration', 4, 1)->nullable();
            $table->string('role', 10);
            $table->json('participants');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(0);
            $table->string('attachment_path')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
};
