<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Paystack-Signature');
        $computed  = hash_hmac('sha512', $request->getContent(), config('services.paystack.secretKey'));

        if (!hash_equals($computed, $signature ?? '')) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $event   = $payload['event'] ?? '';

        if ($event !== 'charge.success') {
            return response()->json(['message' => 'Event ignored'], 200);
        }

        $reference = $payload['data']['reference'] ?? null;

        if (!$reference) {
            Log::error('Paystack webhook: missing reference in payload');
            return response()->json(['message' => 'Bad payload'], 400);
        }

        // Always verify directly with Paystack — never trust webhook body alone
        $verified = Http::withToken(config('services.paystack.secretKey'))
            ->get(config('services.paystack.paymentUrl') . "/transaction/verify/{$reference}")
            ->json();

        if (!($verified['status'] ?? false) || ($verified['data']['status'] ?? '') !== 'success') {
            Log::warning('Paystack webhook: verification failed for reference', ['ref' => $reference]);
            return response()->json(['message' => 'Verification failed'], 200);
        }

        $data        = $verified['data'];
        $metadata    = $data['metadata'] ?? [];
        $paymentType = $metadata['payment_type'] ?? 'buy_direct';

        if ($paymentType === 'boost') {
            $this->activateBoost($reference, (int) $data['id']);
        }

        // Always return 200 — Paystack retries on non-200
        return response()->json(['message' => 'OK'], 200);
    }

    private function activateBoost(string $reference, int $transactionId): void
    {
        $boost = AdvertBoost::where('payment_reference', $reference)
            ->where('payment_status', 'pending')
            ->first();

        if (!$boost) {
            // Already processed or record not found — log and move on
            Log::info('Paystack webhook: boost already processed or not found', ['ref' => $reference]);
            return;
        }

        DB::transaction(function () use ($boost, $transactionId) {
            $boost->update([
                'payment_status' => 'paid',
                'boost_status'   => 'active',
                'trans_id'       => $transactionId,
                'start_date'     => Carbon::now(),
            ]);

            Advert::where('id', $boost->advert_id)->update(['featured' => 'Yes']);
        });

        Log::info('Paystack webhook: boost activated', [
            'boost_id'  => $boost->id,
            'advert_id' => $boost->advert_id,
            'trans_id'  => $transactionId,
        ]);
    }
}
