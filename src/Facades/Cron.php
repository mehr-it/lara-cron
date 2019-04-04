<?php


	namespace MehrIt\LaraCron\Facades;


	use Illuminate\Support\Facades\Facade;
	use MehrIt\LaraCron\Contracts\CronExpression;
	use MehrIt\LaraCron\Contracts\CronManager;
	use MehrIt\LaraCron\Contracts\CronSchedule;

	/**
	 * Class Cron
	 * @package MehrIt\LaraDynamicSchedules\Facades
	 * @method static CronSchedule schedule(CronExpression $cronExpression, $job, string $key = null, string $group = null, $active = true, int $catchUpTimeout = 0) Schedules the given cron job
	 * @method static CronSchedule[] describeAll(string $group = null) Describes all schedules
	 * @method static CronSchedule|null describe(string $key) Describes the schedule with the given key
	 * @method static CronManager delete(string $key) Deletes the given schedule
	 * @method static int|null lastScheduled(string $key) Returns the last time a job was scheduled for
	 * @method static int dispatch(int $period) Dispatches all cron jobs to be run within the given period from now on
	 */
	class Cron extends Facade
	{
		/**
		 * Get the registered name of the component.
		 *
		 * @return string
		 */
		protected static function getFacadeAccessor() {
			return CronManager::class;
		}
	}