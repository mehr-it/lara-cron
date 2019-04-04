<?php


	namespace MehrIt\LaraCron\Contracts;


	interface CronSchedule
	{

		/**
		 * Gets the cron expression describing the execution times
		 * @return CronExpression The cron expression describing the execution times
		 */
		public function getExpression() : CronExpression;

		/**
		 * Sets the cron expressions describing the execution times
		 * @param CronExpression $expression The cron expression describing the execution times
		 * @return CronSchedule This instance
		 */
		public function setExpression(CronExpression $expression) : self;

		/**
		 * Gets the job to be executed
		 * @return CronJob The job
		 */
		public function getJob() : CronJob;

		/**
		 * Sets the job to be executed
		 * @param CronJob $job The job to be executed
		 * @return CronSchedule This instance
		 */
		public function setJob(CronJob $job) : self;

		/**
		 * Gets if the schedule is active
		 * @return bool True if active. Else false.
		 */
		public function isActive() : bool;

		/**
		 * Sets the active state of the schedule
		 * @param bool $active True if active. Else false.
		 * @return CronSchedule This instance
		 */
		public function setActive(bool $active) : self;

		/**
		 * Gets the key identifying the schedule
		 * @return string The key identifying the schedule
		 */
		public function getKey() : string;

		/**
		 * Sets the key identifying the schedule
		 * @param string $key The key
		 * @return CronSchedule This instance
		 */
		public function setKey(string $key) : self;

		/**
		 * Gets the group name for the schedule
		 * @return string|null The group name
		 */
		public function getGroup() : ?string;

		/**
		 * Sets the group name for the
		 * @param string|null $group The group name
		 * @return CronSchedule This instance
		 */
		public function setGroup(?string $group) :self;

		/**
		 * Gets the catchup timeout
		 * @return int The catchup timeout
		 */
		public function getCatchUpTimeout() : int;

		/**
		 * Sets the catchup timeout
		 * @param int $ts The catchup timeout
		 * @return CronSchedule This instance
		 */
		public function setCatchupTimeout(int $ts) : self;


	}