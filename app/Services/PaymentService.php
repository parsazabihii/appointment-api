<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\WebhookException;
use App\Jobs\SendAppointmentConfirmation;
use App\Models\Payment;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private SlotCacheService $cache) {}

    public function handleFakeWebhook(array $payload, string $secret): array
    {
        $expected = (string) config('booking.fake_webhook_secret');
        if ($expected === '' || ! hash_equals($expected, $secret)) {
            throw new WebhookException('Invalid webhook signature.', 401);
        } $eventId = $payload['event_id'];

        return Cache::lock("webhook:fake:{$eventId}", 10)->block(5, function () use ($payload, $eventId) {
            return DB::transaction(function () use ($payload, $eventId) {
                $event = WebhookEvent::where('event_id', $eventId)->lockForUpdate()->first();
                if ($event?->processed_at) {
                    return ['duplicate' => true, 'appointment_id' => $event->payload['appointment_id'] ?? null];
                } if (! $event) {
                    $event = WebhookEvent::create(['event_id' => $eventId, 'provider' => 'fake', 'type' => $payload['type'], 'payload' => $payload]);
                } $payment = Payment::where('external_reference', $payload['external_reference'])->lockForUpdate()->first();
                if (! $payment) {
                    throw new WebhookException('Payment reference not found.', 404);
                } $appointment = $payment->appointment()->lockForUpdate()->first();
                if (in_array($payment->status, [PaymentStatus::Paid, PaymentStatus::Refunded], true)) {
                    $event->update(['processed_at' => now()]);

                    return ['duplicate' => false, 'ignored' => true, 'appointment_id' => $appointment->id, 'status' => $appointment->status->value, 'payment_status' => $payment->status->value];
                } if ($payload['type'] === 'payment.succeeded') {
                    if ($appointment->status !== AppointmentStatus::Pending || $appointment->hold_expires_at?->isPast()) {
                        if ($appointment->status === AppointmentStatus::Pending) {
                            $appointment->update(['status' => AppointmentStatus::Expired]);
                        }$payment->update(['status' => PaymentStatus::Refunded, 'refunded_at' => now()]);
                        $event->update(['processed_at' => now()]);
                        $this->cache->invalidate($appointment->provider_id);

                        return ['duplicate' => false, 'appointment_id' => $appointment->id, 'status' => $appointment->fresh()->status->value, 'payment_status' => PaymentStatus::Refunded->value, 'late' => true];
                    } $payment->update(['status' => PaymentStatus::Paid, 'paid_at' => now()]);
                    $appointment->update(['status' => AppointmentStatus::Confirmed, 'hold_expires_at' => null]);
                    SendAppointmentConfirmation::dispatch($appointment->id)->afterCommit();
                } elseif ($payload['type'] === 'payment.failed') {
                    if ($appointment->status === AppointmentStatus::Pending) {
                        $appointment->update(['status' => AppointmentStatus::Expired]);
                    }$payment->update(['status' => PaymentStatus::Failed]);
                } else {
                    throw new WebhookException('Unsupported webhook event type.', 422);
                } $event->update(['processed_at' => now()]);
                $this->cache->invalidate($appointment->provider_id);

                return ['duplicate' => false, 'appointment_id' => $appointment->id, 'status' => $appointment->fresh()->status->value];
            });
        });
    }
}
