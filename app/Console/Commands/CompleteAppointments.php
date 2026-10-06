<?php
namespace App\Console\Commands;
use App\Enums\AppointmentStatus; use App\Models\Appointment; use Illuminate\Console\Command;
class CompleteAppointments extends Command { protected $signature='appointments:complete'; protected $description='Mark past confirmed appointments as completed.'; public function handle(): int { $count=Appointment::where('status',AppointmentStatus::Confirmed->value)->where('ends_at','<=',now())->update(['status'=>AppointmentStatus::Completed->value]);$this->info("Completed {$count} appointments.");return self::SUCCESS;} }
