<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('appointments:expire-holds')->everyMinute()->withoutOverlapping();
Schedule::command('appointments:dispatch-reminders')->everyMinute()->withoutOverlapping();
Schedule::command('appointments:complete')->everyFiveMinutes()->withoutOverlapping();
