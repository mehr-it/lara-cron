<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.02.19
	 * Time: 00:06
	 */

	namespace MehrIt\LaraCron;


	use Illuminate\Contracts\Bus\QueueingDispatcher;
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
		 * @var QueueingDispatcher
		 */
		protected $dispatcher;

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

					$next = $ts;
					$last = null;

					while ($next && $next !== $last) {

						$lastScheduled = $scheduleLog->getLastSchedule($currSchedule->getKey());

						$last = $next;
						$next = $currSchedule->getExpression()->nextAfter($last, $maxTs);

						// check if we missed a scheduled job and therefore need to catchup
						if ($lastScheduled !== null && $currSchedule->getCatchUpTimeout() > 0) {

							$nextAfterLast = $lastScheduled;
							while (true) {

								// calculate next desired execution time
								$nextAfterLast = $currSchedule->getExpression()->nextAfter($nextAfterLast, $maxTs);

								// stop, if reaching the regular next date
								if ($nextAfterLast === null || $nextAfterLast >= $next)
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
			$job = clone $schedule->getJob();

			// pass cron schedule information
			if (is_object($job) && in_array(InteractsWithCron::class, class_uses_recursive($job))) {
				$job->setCronScheduleKey($schedule->getKey());
				$job->setCronScheduledTs($ts);
				$job->setCronDispatchTs($now);
			}

			// set delay, if desired execution time is in feature
			$delay = $ts - $now;
			if ($delay > 0)
				$job->delay($delay);

			// create dispatch
			$this->getDispatcher()->dispatchToQueue($job);

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

		public function getDispatcher() : QueueingDispatcher {
			if (!$this->dispatcher)
				$this->dispatcher = app(QueueingDispatcher::class);

			return $this->dispatcher;
		}

	}