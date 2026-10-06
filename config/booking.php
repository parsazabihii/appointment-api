<?php

return [
    'hold_minutes' => (int) env('BOOKING_HOLD_MINUTES', 10),
    'cancel_before_hours' => (int) env('BOOKING_CANCEL_BEFORE_HOURS', 6),
    'reminder_hours' => (int) env('BOOKING_REMINDER_HOURS', 24),
    'currency' => env('BOOKING_CURRENCY', 'IRR'),
    'slot_cache_seconds' => 300,
    'fake_webhook_secret' => env('FAKE_PAYMENT_WEBHOOK_SECRET'),
];
