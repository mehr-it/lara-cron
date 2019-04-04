<?php


	namespace MehrIt\LaraCron\Queue;


	use MehrIt\LaraCron\Contracts\CronManager as CronManagerContract;
	use MehrIt\LaraCron\Contracts\CronSchedule;

	trait InteractsWithCron
	{

		/**
		 * @var string|null
		 */
		protected $cronScheduleKey;

		/**
		 * @var int|null
		 */
		protected $cronScheduledTs;

		/**
		 * @var int|null
		 */
		protected $cronDispatchTs;


		/**
		 * Gets the schedule key
		 * @return string The schedule key
		 */
		public function getCronScheduleKey(): ?string {
			return $this->cronScheduleKey;
		}

		/**
		 * Sets the cron schedule key
		 * @param string $cronScheduleKey The cron schedule key
		 * @return InteractsWithCron This instance
		 */
		public function setCronScheduleKey(string $cronScheduleKey) {
			$this->cronScheduleKey = $cronScheduleKey;

			return $this;
		}

		/**
		 * Gets the timestamp the job was scheduled for
		 * @return int The timestamp the job was scheduled for
		 */
		public function getCronScheduledTs(): ?int {
			return $this->cronScheduledTs;
		}

		/**
		 * Sets the timestamp the job was scheduled for
		 * @param int $cronScheduledTs The timestamp the job was scheduled for
		 * @return InteractsWithCron This instance
		 */
		public function setCronScheduledTs(int $cronScheduledTs) {
			$this->cronScheduledTs = $cronScheduledTs;

			return $this;
		}

		/**
		 * Gets the timestamp the cron dispatched the job
		 * @return int|null The timestamp the cron dispatched the job
		 */
		public function getCronDispatchTs(): ?int {
			return $this->cronDispatchTs;
		}

		/**
		 * Sets the timestamp the cron dispatched the job
		 * @param int|null $cronDispatchTs The timestamp the cron dispatched the job
		 * @return InteractsWithCron
		 */
		public function setCronDispatchTs(int $cronDispatchTs) {
			$this->cronDispatchTs = $cronDispatchTs;

			return $this;
		}

		/**
		 * Gets the cron schedule which invoked the job
		 * @return CronSchedule|null The cron schedule if invoked by cron
		 */
		public function getCronSchedule() : ?CronSchedule {

			$key = $this->getCronScheduleKey();

			if ($key) {
				/** @var CronManagerContract $manager */
				$manager = app(CronManagerContract::class);

				// retrieve the schedule
				return $manager->describe($key);
			}

			return null;
		}






	}