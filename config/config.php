<?php

	use MehrIt\LaraCron\Log\CronScheduleLogEntry;
	use MehrIt\LaraCron\Store\CronTabEntry;

	return [

		/*
		|--------------------------------------------------------------------------
		| Dispatch schedule
		|--------------------------------------------------------------------------
		|
		| This option activates cron job dispatch using laravel's scheduler.
		|
		| You may disable the dispatch schedule, but then you have to regularly call
		| "artisan cron:dispatch 600" to invoke dispatch of cron jobs.
		|
		*/

		'dispatch_schedule' => true,

		/*
		|--------------------------------------------------------------------------
		| Cron store
		|--------------------------------------------------------------------------
		|
		| This option configures the store for all scheduled cron jobs
		|
		*/

		'store' => [
			'driver' => env('CRON_STORE', 'eloquent'),
			'model'  => CronTabEntry::class
		],

		/*
		|--------------------------------------------------------------------------
		| Cron Schedule log
		|--------------------------------------------------------------------------
		|
		| This option configures schedule log which holds information about already
		| dispatched cron schedules
		|
		*/

		'scheduleLog' => [
			'driver' => env('CRON_SCHEDULE_LOG', 'eloquent'),
			'model'  => CronScheduleLogEntry::class,
		],

	];
