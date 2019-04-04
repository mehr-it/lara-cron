<?php


	namespace MehrIt\LaraCron\Log;


	use MehrIt\LaraCron\Contracts\CronScheduleLog;

	/**
	 * Logs cron schedules to memory. Useful for testing only
	 * @package MehrIt\LaraDynamicSchedules\Log
	 */
	class MemoryScheduleLog implements CronScheduleLog
	{
		protected $logEntries = [];

		/**
		 * @inheritDoc
		 */
		public function log(string $key, int $scheduledFor): CronScheduleLog {

			$last = $this->getLastSchedule($key);

			if ($last === null || $scheduledFor > $last)
				$this->logEntries[$key] = $scheduledFor;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getLastSchedule(string $key): ?int {

			return $this->logEntries[$key] ?? null;
		}

	}