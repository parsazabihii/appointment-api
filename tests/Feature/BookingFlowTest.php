<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $providerUser = User::create(['name' => 'Provider', 'email' => 'p@test.com', 'password' => 'password123', 'role' => UserRole::Provider]);
        $provider = $providerUser->provider()->create(['timezone' => 'UTC', 'is_active' => true]);
        $service = Service::create(['name' => 'Consultation', 'duration_minutes' => 30, 'is_active' => true]);
        $provider->services()->attach($service->id, ['price' => 500000, 'is_active' => true]);
        $date = CarbonImmutable::now('UTC')->addDays(2)->startOfDay();
        $provider->availabilities()->create(['day_of_week' => $date->dayOfWeek, 'start_time' => '09:00', 'end_time' => '12:00']);
        $start = $date->setTime(10, 0)->toIso8601String();

        return [$provider, $service, $start];
    }

    public function test_booking_creates_pending_hold_and_payment(): void
    {
        [$p,$s,$start] = $this->setupData();
        $u = User::create(['name' => 'C1', 'email' => 'c1@test.com', 'password' => 'password123', 'role' => UserRole::Customer]);
        Sanctum::actingAs($u);
        $r = $this->withHeader('Idempotency-Key', 'booking-1')->postJson('/api/appointments', ['provider_id' => $p->id, 'service_id' => $s->id, 'starts_at' => $start]);
        $r->assertCreated()->assertJsonPath('data.status', 'pending')->assertJsonPath('data.payment.status', 'pending');
        $this->assertDatabaseCount('appointments', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_second_user_cannot_book_same_slot(): void
    {
        [$p,$s,$start] = $this->setupData();
        $a = User::create(['name' => 'A', 'email' => 'a@test.com', 'password' => 'password123', 'role' => UserRole::Customer]);
        $b = User::create(['name' => 'B', 'email' => 'b@test.com', 'password' => 'password123', 'role' => UserRole::Customer]);
        Sanctum::actingAs($a);
        $this->postJson('/api/appointments', ['provider_id' => $p->id, 'service_id' => $s->id, 'starts_at' => $start])->assertCreated();
        Sanctum::actingAs($b);
        $this->postJson('/api/appointments', ['provider_id' => $p->id, 'service_id' => $s->id, 'starts_at' => $start])->assertStatus(409);
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_successful_webhook_confirms_appointment_and_is_idempotent(): void
    {
        [$p,$s,$start] = $this->setupData();
        $u = User::create(['name' => 'C', 'email' => 'c@test.com', 'password' => 'password123', 'role' => UserRole::Customer]);
        Sanctum::actingAs($u);
        $book = $this->postJson('/api/appointments', ['provider_id' => $p->id, 'service_id' => $s->id, 'starts_at' => $start])->assertCreated();
        $appointmentId = $book->json('data.id');
        $ref = $book->json('data.payment.external_reference');
        $payload = ['event_id' => 'evt_1', 'type' => 'payment.succeeded', 'external_reference' => $ref, 'appointment_id' => $appointmentId];
        $this->withHeader('X-Webhook-Secret', 'test-secret')->postJson('/api/webhooks/payments/fake', $payload)->assertOk()->assertJsonPath('data.status', 'confirmed');
        $this->withHeader('X-Webhook-Secret', 'test-secret')->postJson('/api/webhooks/payments/fake', $payload)->assertOk()->assertJsonPath('data.duplicate', true);
        $this->assertDatabaseHas('appointments', ['id' => $appointmentId, 'status' => 'confirmed']);
    }

    private function createConfirmedBooking(): array
    {
        [$provider, $service, $start] = $this->setupData();
        $user = User::create(['name' => 'Customer', 'email' => 'webhook@test.com', 'password' => 'password123', 'role' => UserRole::Customer]);
        Sanctum::actingAs($user);
        $booking = $this->postJson('/api/appointments', ['provider_id' => $provider->id, 'service_id' => $service->id, 'starts_at' => $start])->assertCreated();
        $payload = ['event_id' => 'first-success', 'type' => 'payment.succeeded', 'external_reference' => $booking->json('data.payment.external_reference'), 'appointment_id' => $booking->json('data.id')];
        $this->withHeader('X-Webhook-Secret', 'test-secret')->postJson('/api/webhooks/payments/fake', $payload)->assertOk();

        return $payload;
    }

    public function test_success_with_new_event_id_does_not_refund_confirmed_payment(): void
    {
        $payload = $this->createConfirmedBooking();
        $payload['event_id'] = 'second-success';
        $this->withHeader('X-Webhook-Secret', 'test-secret')->postJson('/api/webhooks/payments/fake', $payload)->assertOk();
        $this->assertDatabaseHas('payments', ['appointment_id' => $payload['appointment_id'], 'status' => 'paid', 'refunded_at' => null]);
        $this->assertDatabaseHas('appointments', ['id' => $payload['appointment_id'], 'status' => 'confirmed']);
        $this->assertDatabaseHas('webhook_events', ['event_id' => 'second-success']);
    }

    public function test_failure_after_success_does_not_downgrade_paid_payment(): void
    {
        $payload = $this->createConfirmedBooking();
        $payload['event_id'] = 'late-failure';
        $payload['type'] = 'payment.failed';
        $this->withHeader('X-Webhook-Secret', 'test-secret')->postJson('/api/webhooks/payments/fake', $payload)->assertOk();
        $this->assertDatabaseHas('payments', ['appointment_id' => $payload['appointment_id'], 'status' => 'paid']);
        $this->assertDatabaseHas('appointments', ['id' => $payload['appointment_id'], 'status' => 'confirmed']);
    }

    public function test_failure_after_refund_does_not_erase_refunded_state(): void
    {
        $payload = $this->createConfirmedBooking();
        $this->postJson('/api/appointments/'.$payload['appointment_id'].'/cancel', [])->assertOk();
        $payload['event_id'] = 'failure-after-refund';
        $payload['type'] = 'payment.failed';
        $this->withHeader('X-Webhook-Secret', 'test-secret')->postJson('/api/webhooks/payments/fake', $payload)->assertOk();
        $this->assertDatabaseHas('payments', ['appointment_id' => $payload['appointment_id'], 'status' => 'refunded']);
    }
}
