<?php


	namespace MehrIt\LaraCron\Contracts;


	use Traversable;

	interface CronManager
	{

		/**
		 * Schedules the given cron job
		 * @param CronExpression $cronExpression The cron expression which determines the execution time
		 * @param CronJob $job The task/job to run
		 * @param string|null $key The key identifying the schedule. If omitted random key will be generated
		 * @param string|null $group The schedule group. This allows to describe all schedules by a given group
		 * @param bool $active True if the schedule should be marked as active
		 * @param int $catchUpTimeout The number of seconds, the missed jobs should be caught up
		 * @return CronSchedule The created schedule
		 */
		public function schedule(CronExpression $cronExpression, CronJob $job, string $key = null, string $group = null, $active = true, int $catchUpTimeout = 0) : CronSchedule;

		/**
		 * Describes all schedules
		 * @param string|null $group If set, only schedules of the given group are returned
		 * @return CronSchedule[]|Traversable The schedules
		 */
		public function describeAll(string $group = null) : Traversable ;

		/**
		 * Describes the schedule with the given key
		 * @param string $key The key
		 * @return CronSchedule|null The schedule or null of not existing
		 */
		public function describe(string $key) :?CronSchedule;

		/**
		 * Deletes the given schedule
		 * @param string $key The schedule's key
		 * @return CronManager This instance
		 */
		public function delete(string $key) : self;

		/**
		 * Returns the last time a job was scheduled for
		 * @param string $key The schedule's key
		 * @return int|null The last scheduled time or null if yet not scheduled
		 */
		public function lastScheduled(string $key) : ?int;


		/**
		 * Dispatches all cron jobs to be run within the given period from now on
		 * @param int $period The period in seconds
		 * @return int The number of jobs dispatched
		 */
		public function dispatch(int $period) : int;

	}