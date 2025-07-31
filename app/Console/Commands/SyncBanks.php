<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Bank;

class SyncBanks extends Command
{
    protected $signature = 'banks:sync';
    protected $description = 'Sync Nigerian banks from Paystack API';

    public function handle()
    {
        $response = Http::withToken(config('services.paystack.secret'))
            ->get('https://api.paystack.co/bank?currency=NGN');

        if (!$response->ok()) {
            $this->error('Failed to fetch banks from Paystack.');
            return 1;
        }

        $banks = $response->json('data');

        foreach ($banks as $bank) {
            Bank::updateOrCreate(
                ['paystack_bank_code' => $bank['code']],
                ['name' => $bank['name']]
            );
        }

        $this->info('Bank list synced successfully.');
        return 0;
    }
}

