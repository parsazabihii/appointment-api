<?php
namespace App\Notifications;
use App\Models\Appointment; use Illuminate\Bus\Queueable; use Illuminate\Notifications\Messages\MailMessage; use Illuminate\Notifications\Notification;
class AppointmentCancelled extends Notification { use Queueable; public function __construct(private Appointment $appointment){} public function via(object $notifiable): array{return ['mail','database'];} public function toMail(object $notifiable): MailMessage { return (new MailMessage)->subject('Appointment cancelled')->line('Your appointment #'.$this->appointment->id.' has been cancelled.'); } public function toArray(object $notifiable): array{return ['appointment_id'=>$this->appointment->id,'type'=>'cancelled'];} }
