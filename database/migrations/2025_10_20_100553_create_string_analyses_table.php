<?php
// database/migrations/xxxx_create_string_analyses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('string_analyses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->text('value');
            $table->json('properties');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('string_analyses');
    }
};
