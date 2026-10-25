<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Salary dropdown values are stored verbatim, so they must match the new "USh" option labels.
    public function up(): void
    {
        foreach (['salary', 'expected_salary'] as $column) {
            DB::table('adverts')
                ->where($column, 'like', '%UGX%')
                ->update([$column => DB::raw("REPLACE({$column}, 'UGX', 'USh')")]);
        }
    }

    public function down(): void
    {
        foreach (['salary', 'expected_salary'] as $column) {
            DB::table('adverts')
                ->where($column, 'like', '%USh%')
                ->update([$column => DB::raw("REPLACE({$column}, 'USh', 'UGX')")]);
        }
    }
};
