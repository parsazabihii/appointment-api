<?php
namespace App\Jobs;
use App\Models\Appointment; use App\Notifications\AppointmentCancelled; use Illuminate\Contracts\Queue\ShouldQueue; use Illuminate\Foundation\Queue\Queueable;
class SendAppointmentCancellation implements ShouldQueue { use Queueable; public int $tries=3; public function __construct(public int $appointmentId){} public function handle(): void { $a=Appointment::with(['user','provider.user','service'])->find($this->appointmentId); if($a){$a->user->notify(new AppointmentCancelled($a));} } }
