<?php


	namespace MehrIt\LaraCron\Queue;


	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Bus\Dispatcher;
	use Illuminate\Contracts\Queue\Job;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Foundation\Bus\Dispatchable;
	use Illuminate\Queue\InteractsWithQueue;
	use MehrIt\LaraCron\Contracts\CronManager as CronManagerContract;

	/**
	 * Implements a cron queue job
	 * @package MehrIt\LaraDynamicSchedules
	 */
	class CronJob implements ShouldQueue
	{
		use InteractsWithQueue;
		use Dispatchable;
		use Queueable;

		/**
		 * @var object|InteractsWithQueue
		 */
		protected $task;

		/**
		 * @var string
		 */
		protected $scheduleKey;

		/**
		 * @var int
		 */
		protected $scheduledTs;

		/**
		 * Creates a new instance
		 * @param object $task The task to run
		 * @param string $scheduleKey The key of the schedule which invoked the job
		 * @param int $scheduledTs The timestamp the job was scheduled for
		 */
		public function __construct($task, string $scheduleKey, int $scheduledTs) {
			$this->task        = $task;
			$this->scheduleKey = $scheduleKey;
			$this->scheduledTs = $scheduledTs;
		}

		/**
		 * Gets the task to execute by this cron
		 * @return mixed The task to execute
		 */
		public function getTask() {
			return $this->task;
		}

		/**
		 * Gets the schedule key
		 * @return string The schedule key
		 */
		public function getScheduleKey(): string {
			return $this->scheduleKey;
		}

		/**
		 * Gets the timestamp the job was scheduled for
		 * @return int The timestamp the job was scheduled for
		 */
		public function getScheduledTs(): int {
			return $this->scheduledTs;
		}




		/**
		 * Handles the cron job
		 * @param Dispatcher $dispatcher The BUS dispatcher
		 */
		public function handle(Dispatcher $dispatcher) {

			if ($this->shouldRun()) {

				$task = $this->task;

				// set job if required
				$this->setJobInstanceIfNecessary($this->job, $task);

				// run the task now
				$dispatcher->dispatchNow($task);
			}

		}

		/**
		 * Determines if the cron job should run or not
		 * @return bool True if should run. Else false.
		 */
		protected function shouldRun() : bool {

			/** @var CronManagerContract $manager */
			$manager = app(CronManagerContract::class);

			// retrieve the schedule
			$schedule = $manager->describe($this->scheduleKey);

			// check criteria for the job to be run, these are
			// * schedule exists (was not deleted meanwhile)
			// * schedule is still active (could be deactivated meanwhile)
			// * schedule still would invoke the cron job at the now scheduled time (could be modified meanwhile)
			return
				$schedule
				&& $schedule->isActive()
				&& $schedule->getExpression()->nextAfter($this->scheduledTs - 1, $this->scheduledTs + 1) == $this->scheduledTs;
		}

		/**
		 * Set the job instance of the given class if necessary.
		 *
		 * @param Job $job
		 * @param mixed $instance
		 * @return mixed
		 */
		protected function setJobInstanceIfNecessary(Job $job, $instance) {
			if (in_array(InteractsWithQueue::class, class_uses_recursive($instance))) {
				$instance->setJob($job);
			}

			return $instance;
		}


	}