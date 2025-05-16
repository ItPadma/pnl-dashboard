<?php

use App\Jobs\PajakKeluaranDetailJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new PajakKeluaranDetailJob)->dailyAt('05:00');
