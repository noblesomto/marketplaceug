<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `trans_id` already holds the buyer-side Flutterwave transaction id
     * captured at purchase time — reusing it for the seller payout transfer
     * would silently destroy that audit trail. This adds a dedicated column
     * so the payout transfer's Flutterwave id can be recorded without
     * overwriting the original purchase transaction id.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payout_trans_id')->nullable()->after('settlement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payout_trans_id');
        });
    }
};
