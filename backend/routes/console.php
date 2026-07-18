<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('checkin:purge-expired-tokens')->hourly();
Schedule::command('checkin:ses-retry-failed')->hourly();
Schedule::command('checkin:sync-integrations')->everyFifteenMinutes();
