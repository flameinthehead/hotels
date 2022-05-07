<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TelegramRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telegram_requests', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->integer('adults')->nullable();
            $table->char('is_finished', 1)->default('0');
            $table->unsignedBigInteger('telegram_from_id')->nullable();
            $table->string('status')->default('new');
            $table->string('last_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telegram_requests');
    }
}
