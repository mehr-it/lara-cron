<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.02.19
	 * Time: 00:06
	 */

	namespace MehrIt\LaraCron;


	use Illuminate\Support\Str;
	use MehrIt\LaraCron\Contracts;
	use MehrIt\LaraCron\Contracts\CronExpression;
	use MehrIt\LaraCron\Contracts\CronManager as CronManagerContract;
	use MehrIt\LaraCron\Contracts\CronSchedule;
	use MehrIt\LaraCron\Contracts\CronScheduleLog;
	use MehrIt\LaraCron\Contracts\CronStore;
	use MehrIt\LaraCron\Queue\InteractsWithCron;
	use Traversable;

	/**
	 * Manages cron schedules
	 * @package MehrIt\LaraDynamicSchedules
	 */
	class CronManager implements CronManagerContract
	{
		use CreatesCronStore;
		use CreatesCronScheduleLog;

		/**
		 * @var CronStore
		 */
		protected $store;

		/**
		 * @var CronScheduleLog
		 */
		protected $scheduleLog;


		/**
		 * @inheritDoc
		 */
		public function schedule(CronExpression $cronExpression, Contracts\CronJob $job, string $key = null, string $group = null, $active = true, int $catchUpTimeout = 0): CronSchedule {

			$schedule = null;
			$store    = $this->getStore();

			// generate new key or fetch existing schedule
			if (!$key)
				$key = Str::orderedUuid();

			// if yet not existing, we create a new schedule
			$schedule = app(CronSchedule::class, [
				'expression'     => $cronExpression,
				'job'            => $job,
				'key'            => $key,
				'group'          => $group,
				'active'         => $active,
				'catchupTimeout' => $catchUpTimeout,
			]);

			// save the schedule
			$store->put($schedule);

			return $schedule;
		}

		/**
		 * @inheritDoc
		 */
		public function describeAll(string $group = null): Traversable {
			return $this->getStore()->all($group);
		}

		/**
		 * @inheritDoc
		 */
		public function describe(string $key): ?CronSchedule {
			return $this->getStore()->get($key);
		}

		/**
		 * @inheritDoc
		 */
		public function delete(string $key): Contracts\CronManager {
			$this->getStore()->delete($key);

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function lastScheduled(string $key): ?int {
			return $this->getScheduleLog()->getLastSchedule($key);
		}

		/**
		 * @inheritDoc
		 */
		public function dispatch(int $period): int {

			$dispatchCount = 0;

			$ts    = time();
			$maxTs = $ts + $period;

			$scheduleLog = $this->getScheduleLog();


			foreach($this->getStore()->all() as $currSchedule) {

				if ($currSchedule->isActive()) {

					$lastScheduled = $scheduleLog->getLastSchedule($currSchedule->getKey());
					$next = $currSchedule->getExpression()->nextAfter($ts, $maxTs);

					// check if we missed a scheduled job and therefore need to catchup
					if ($lastScheduled !== null && $currSchedule->getCatchUpTimeout() > 0) {

						$nextAfterLast = $lastScheduled;
						while (true) {

							// calculate next desired execution time
							$nextAfterLast = $currSchedule->getExpression()->nextAfter($nextAfterLast, $maxTs);

							// stop, if reaching the regular next date
							if ($nextAfterLast >= $next)
								break;

							// if within catchup timeout, we schedule the missed job
							if ($nextAfterLast > $ts - $currSchedule->getCatchUpTimeout()) {
								$this->dispatchFor($currSchedule, $nextAfterLast);
								++$dispatchCount;
							}
						}
					}

					// schedule if we have a new schedule time
					if ($next > $lastScheduled) {
						$this->dispatchFor($currSchedule, $next);
						++$dispatchCount;
					}

				}

			}

			return $dispatchCount;
		}

		/**
		 * Dispatches a job for the given schedule to be executed at given timestamp
		 * @param CronSchedule $schedule The schedule
		 * @param int $ts The timestamp the job should be executed
		 */
		protected function dispatchFor(CronSchedule $schedule, int $ts) {

			$now = time();

			/** @var mixed|InteractsWithCron $job */
			$job = $schedule->getJob();

			// pass cron schedule information
			if (is_object($job) && in_array(InteractsWithCron::class, class_uses_recursive($job))) {
				$job->setCronScheduleKey($schedule->getKey());
				$job->setCronScheduledTs($ts);
				$job->setCronDispatchTs($now);
			}

			// create dispatch
			$pendingDispatch = dispatch($job);

			// set delay, if desired execution time is in feature
			$delay = $ts - $now;
			if ($delay > 0)
				$pendingDispatch->delay($delay);

			// explicit destruct, so pending dispatch is flushed
			$pendingDispatch = null;

			// log the timestamp
			$this->getScheduleLog()->log($schedule->getKey(), $ts);
		}

		/**
		 * Gets the store instance
		 * @return CronStore The store instance
		 */
		public function getStore() : CronStore {
			if (!$this->store)
				$this->store = $this->makeStore(config('cron.store'));

			return $this->store;
		}

		/**
		 * Gets the log instance
		 * @return CronScheduleLog The log instance
		 */
		public function getScheduleLog() : CronScheduleLog {
			if (!$this->scheduleLog)
				$this->scheduleLog = $this->makeScheduleLog(config('cron.scheduleLog'));

			return $this->scheduleLog;
		}

	}