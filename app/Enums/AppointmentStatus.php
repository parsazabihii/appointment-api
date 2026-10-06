<?php
namespace App\Enums;
enum AppointmentStatus: string { case Pending = 'pending'; case Confirmed = 'confirmed'; case Cancelled = 'cancelled'; case Completed = 'completed'; case Expired = 'expired'; }
