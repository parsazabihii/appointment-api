<?php
namespace App\Policies;
use App\Enums\UserRole; use App\Models\Appointment; use App\Models\User;
class AppointmentPolicy { public function view(User $user,Appointment $appointment): bool { return $user->role===UserRole::Admin||$appointment->user_id===$user->id||$appointment->provider?->user_id===$user->id; } public function cancel(User $user,Appointment $appointment): bool { return $this->view($user,$appointment); } public function reschedule(User $user,Appointment $appointment): bool { return $this->view($user,$appointment); } }
