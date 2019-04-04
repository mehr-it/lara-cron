<?php


	namespace MehrIt\LaraCron\Contracts;


	interface CronScheduleLog
	{
		/**
		 * Logs a cron schedule
		 * @param string $key The cron key
		 * @param int $scheduledFor The timestamp the cron was scheduled for
		 * @return CronScheduleLog
		 */
		public function log(string $key, int $scheduledFor) :self;

		/**
		 * Gets the last schedule for the given cron
		 * @param string $key The cron key
		 * @return int|null The last times the cron was scheduled for. Null if yet not scheduled
		 */
		public function getLastSchedule(string $key) : ?int;
	}