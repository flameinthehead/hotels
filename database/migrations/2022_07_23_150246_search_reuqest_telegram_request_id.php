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
        Schema::table('search_requests', function (Blueprint $table) {
            $table
                ->foreignId('telegram_request_id')
                ->constrained()
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_requests', function (Blueprint $table) {
            $table->dropForeign('telegram_request_id');
            $table->dropColumn('telegram_request_id');
        });
    }
};
