<?php


	namespace MehrIt\LaraCron\Contracts;


	interface CronStore
	{

		/**
		 * Gets the cron schedule with given key
		 * @param string $key The key
		 * @return CronSchedule|null The schedule or null if not found
		 */
		public function get(string $key) : ?CronSchedule;

		/**
		 * Lists all schedules
		 * @param string|null $group If set, only schedules of the given group are returned
		 * @return CronSchedule[]|\Traversable The schedules
		 */
		public function all(string $group = null) : \Traversable;

		/**
		 * Puts the given cron schedule to store. If schedule key already exists, it is overridden.
		 * @param CronSchedule $schedule The schedule
		 * @return CronStore This instance
		 */
		public function put(CronSchedule $schedule) : self;

		/**
		 * Deletes the cron schedule with the given key
		 * @param string $key The key
		 * @return CronStore This instance
		 */
		public function delete(string $key) :self;

	}