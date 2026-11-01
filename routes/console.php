<?php


use App\Core\Inventory\EvaluateExpiredReservationJob;
use App\Core\Product\EvaluatePublishedProductCollectionJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new EvaluateExpiredReservationJob())->everyTenMinutes();
Schedule::job(new EvaluatePublishedProductCollectionJob())->cron('0 * * * *');
