# Architecture Notes

## 1. Why slots are not stored
Slots are derived from weekly availability + service duration - blocked times - active appointments. This avoids generating thousands of future rows and makes schedule changes immediately meaningful.

## 2. Timezone strategy
- `availabilities.start_time/end_time`: provider-local recurring wall-clock times.
- `blocked_times` and `appointments`: real instants stored as timezone-aware timestamps (PostgreSQL `timestamptz`).
- API slots expose UTC ISO timestamps plus local display values.

## 3. Why appointment stores duration and price
`duration_minutes` and `price` are snapshots. If the service duration or provider price changes later, historical appointments retain the contract that existed when the booking was created.

## 4. Double-booking defense
The API does not trust a slot that was shown seconds ago. When booking arrives it:
1. obtains a per-provider atomic cache lock;
2. starts a DB transaction;
3. obtains a PostgreSQL transaction advisory lock;
4. expires stale pending holds;
5. regenerates current availability without cache;
6. inserts the pending appointment;
7. PostgreSQL GIST exclusion constraint rejects any final overlap.

## 5. Temporary holds
A booking is initially `pending` for a configurable time. This prevents two customers from paying for the same slot while allowing an abandoned payment to release automatically.

## 6. Payment webhook idempotency
Provider event IDs are unique and webhook processing is additionally protected by a cache lock. Replayed `payment.succeeded` events return the prior result instead of applying the transition twice.

## 7. Cache invalidation
Instead of deleting wildcard keys, each provider owns a `slots-version`. Slot cache keys include the current version. Schedule/appointment/payment changes increment the version, making all old slot keys unreachable naturally.

## 8. Queue boundary
Emails/database notifications are sent from queued Jobs, not inside the HTTP request. Confirmation and cancellation Jobs are scheduled after DB commit so workers do not observe uncommitted records.

## 9. Scheduler responsibilities
- expire abandoned holds;
- enqueue reminders;
- mark past confirmed appointments completed.

## 10. What a real payment gateway would change
Only the gateway adapter/webhook signature and provider-specific payload mapping should change. Appointment state transitions stay in the PaymentService, keeping provider-specific transport details out of booking logic.
