<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('followers', function (Blueprint $table) {
            // Drop old FKs if they exist
            $table->dropForeign(['user_id']);
            $table->dropForeign(['follow']);

            // Ensure correct column types
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('follow')->change();

            // Add new FKs to users.id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('follow')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['follow']);
        });
    }

};
