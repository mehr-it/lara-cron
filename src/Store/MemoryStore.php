<?php


	namespace MehrIt\LaraCron\Store;


	use MehrIt\LaraCron\Contracts\CronSchedule;
	use MehrIt\LaraCron\Contracts\CronStore;

	/**
	 * Stores cron schedules in memory. Only useful for testing
	 * @package MehrIt\LaraDynamicSchedules\Store
	 */
	class MemoryStore implements CronStore
	{
		/**
		 * @var CronSchedule[]
		 */
		protected $schedules = [];

		/**
		 * @inheritDoc
		 */
		public function get(string $key): ?CronSchedule {
			return $this->schedules[$key] ?? null;
		}

		/**
		 * @inheritDoc
		 */
		public function all(string $group = null): \Traversable {

			foreach($this->schedules as $curr) {
				if ($group === null || $curr->getGroup() == $group)
					yield $curr;
			}
		}

		/**
		 * @inheritDoc
		 */
		public function put(CronSchedule $schedule): CronStore {
			if (!$schedule->getKey())
				throw new \InvalidArgumentException('Missing key for schedule');

			$this->schedules[$schedule->getKey()] = $schedule;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function delete(string $key): CronStore {

			if ($this->schedules[$key] ?? null)
				unset($this->schedules[$key]);

			return $this;

		}


	}