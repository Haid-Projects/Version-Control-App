<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('path');
            $table->boolean('isAvailable')->default(1);
            $table->bigInteger('version')->default(0)->nullable();
            $table->integer('reservation_holder')->nullable();
            $table->text('diff')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('group_id')->nullable()->constrained('groups');
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
        Schema::dropIfExists('files');
    }
};
