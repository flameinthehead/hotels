<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Cities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique()->index();
            $table->string('name', 100);
            $table->string('en_name', 100);
            $table->float('lon');
            $table->float('lat');
            $table->string('timezone', 50);
            $table->foreignId('country_id')->constrained()->onDelete('CASCADE');
            $table->string('case_ro', 100)->nullable();
            $table->string('case_da', 100)->nullable();
            $table->string('case_vi', 100)->nullable();
            $table->string('case_tv', 100)->nullable();
            $table->string('case_pr', 100)->nullable();
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
        Schema::drop('cities');
    }
}
