<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('payout_method')->nullable()->after('account_number');
            $table->string('mobile_network')->nullable()->after('payout_method');
            $table->string('mobile_money_number', 15)->nullable()->after('mobile_network');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'mobile_network', 'mobile_money_number']);
        });
    }
};
