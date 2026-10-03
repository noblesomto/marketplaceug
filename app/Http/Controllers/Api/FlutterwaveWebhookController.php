<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\UnmatchedPaymentMail;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use App\Models\UnmatchedPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('verif-hash');
        $expected  = config('services.flutterwave.webhookHash');

        if (blank($expected)) {
            // Server misconfiguration, not an attack — returning 200 here
            // would tell Flutterwave the event was delivered successfully,
            // so it would never retry and the event would be lost for good.
            Log::error('Flutterwave webhook rejected: webhook secret unconfigured');
            return response()->json(['message' => 'Server misconfigured'], 500);
        }

        if (!hash_equals((string) $expected, (string) $signature)) {
            Log::error('Flutterwave webhook rejected: invalid signature', ['ip' => $request->ip()]);
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
            if (AdvertBoost::where('payment_reference', $reference)->exists()) {
                $paymentType = 'boost';
            } elseif (Payment::where('payment_reference', $reference)->exists()) {
                $paymentType = 'buy_direct';
            } else {
                // Flutterwave confirms money changed hands, but nothing in our DB
                // references it (e.g. a client paid directly against Flutterwave
                // without ever calling our initialize endpoint, so no
                // AdvertBoost/Payment row was ever created). Don't guess — alert
                // an admin so the customer can be manually credited.
                $this->reportUnmatchedPayment($reference, $verifiedData);
                return response()->json(['message' => 'OK'], 200);
            }

            Log::info('Flutterwave webhook: inferred payment_type from reference lookup', [
                'ref'  => $reference,
                'type' => $paymentType,
            ]);
        }

        if ($paymentType === 'boost') {
            $this->activateBoost(
                $reference,
                (int) $verifiedData['id'],
                $verifiedData['amount'] ?? null,
                $verifiedData['currency'] ?? null
            );
        }

        // Always return 200 — Flutterwave retries on non-200
        return response()->json(['message' => 'OK'], 200);
    }

    private function reportUnmatchedPayment(string $reference, array $data): void
    {
        Log::critical('Flutterwave webhook: paid transaction has no matching AdvertBoost or Payment record', [
            'ref'    => $reference,
            'amount' => $data['amount'] ?? null,
            'email'  => $data['customer']['email'] ?? null,
        ]);

        $unmatched = UnmatchedPayment::firstOrCreate(
            ['reference' => $reference],
            [
                'transaction_id' => $data['id'] ?? null,
                'amount'         => $data['amount'] ?? 0,
                'customer_email' => $data['customer']['email'] ?? 'unknown',
                'paid_at'        => $data['paid_at'] ?? null,
                'status'         => 'unresolved',
            ]
        );

        if (!$unmatched->wasRecentlyCreated) {
            // Already recorded (and already alerted) on a previous delivery of this event.
            return;
        }

        Mail::to(config('global.admin_email'))->queue(new UnmatchedPaymentMail([
            'reference'       => $reference,
            'transaction_id'  => $data['id'] ?? null,
            'amount'          => $data['amount'] ?? 0,
            'customer_email'  => $data['customer']['email'] ?? 'unknown',
            'paid_at'         => $data['paid_at'] ?? 'unknown',
        ]));
    }

    private function activateBoost(string $reference, int $transactionId, $amount = null, $currency = null): void
    {
        $boost = AdvertBoost::where('payment_reference', $reference)
            ->where('payment_status', 'pending')
            ->first();

        if (!$boost) {
            // Already processed or record not found — log and move on
            Log::info('Flutterwave webhook: boost already processed or not found', ['ref' => $reference]);
            return;
        }

        if (round((float) $amount) < round((float) $boost->amount) || $currency !== config('currency.code')) {
            // Verified as "successful" by Flutterwave but the amount/currency
            // doesn't match what this boost expects — do not activate. Log
            // and move on (same non-retriable treatment as "already
            // processed or not found" above); a genuine payment for this
            // reference would never fail this check.
            Log::warning('Flutterwave webhook: boost amount/currency mismatch, not activating', [
                'ref'      => $reference,
                'boost_id' => $boost->id,
                'expected' => $boost->amount,
                'got'      => $amount,
                'currency' => $currency,
            ]);
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
