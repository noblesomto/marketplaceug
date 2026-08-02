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

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('verif-hash');
        $expected  = config('services.flutterwave.webhookHash');

        if (!hash_equals((string) $expected, (string) $signature)) {
            Log::warning('Flutterwave webhook: invalid signature', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 200);
        }

        $payload = $request->json()->all();
        $event   = $payload['event'] ?? '';

        if ($event !== 'charge.completed') {
            return response()->json(['message' => 'Event ignored'], 200);
        }

        $reference = $payload['data']['reference'] ?? null;

        if (!$reference) {
            Log::error('Flutterwave webhook: missing reference in payload');
            return response()->json(['message' => 'Bad payload'], 200);
        }

        // Always verify directly with Flutterwave — never trust webhook body alone
        $response = Http::withToken(config('services.flutterwave.secretKey'))
            ->get(config('services.flutterwave.paymentUrl') . '/transactions/verify_by_reference', [
                'tx_ref' => $reference,
            ]);

        $data = $response->json();

        if (!($data['status'] === 'success' && ($data['data']['status'] ?? '') === 'successful')) {
            Log::warning('Flutterwave webhook: verification failed for reference', ['ref' => $reference]);
            return response()->json(['message' => 'Verification failed'], 200);
        }

        $verifiedData = $data['data'];
        $metadata     = $verifiedData['metadata'] ?? [];
        $paymentType  = $metadata['payment_type'] ?? null;

        // If payment_type is missing from metadata, infer from the reference —
        // web boost flows historically omitted this field.
        if (!$paymentType) {
            $paymentType = AdvertBoost::where('payment_reference', $reference)->exists()
                ? 'boost'
                : 'buy_direct';

            Log::info('Flutterwave webhook: inferred payment_type from reference lookup', [
                'ref'  => $reference,
                'type' => $paymentType,
            ]);
        }

        if ($paymentType === 'boost') {
            $this->activateBoost($reference, (int) $verifiedData['id']);
        }

        // Always return 200 — Flutterwave retries on non-200
        return response()->json(['message' => 'OK'], 200);
    }

    private function activateBoost(string $reference, int $transactionId): void
    {
        $boost = AdvertBoost::where('payment_reference', $reference)
            ->where('payment_status', 'pending')
            ->first();

        if (!$boost) {
            // Already processed or record not found — log and move on
            Log::info('Flutterwave webhook: boost already processed or not found', ['ref' => $reference]);
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

        Log::info('Flutterwave webhook: boost activated', [
            'boost_id'  => $boost->id,
            'advert_id' => $boost->advert_id,
            'trans_id'  => $transactionId,
        ]);
    }
}
