<?php


	namespace MehrIt\LaraCron\Queue;


	use Illuminate\Queue\Events\JobProcessed;
	use MehrIt\LaraCron\Contracts\CronManager;

	class BeforeJobHandler
	{


		/**
		 * @var CronManager
		 */
		protected $manager;

		/**
		 * BeforeJobHandler constructor.
		 * @param CronManager $manager
		 */
		public function __construct(CronManager $manager) {
			$this->manager = $manager;
		}


		public function handle(JobProcessed $event) {

			$job = $event->job;

			if ($job instanceof \MehrIt\LaraCron\Contracts\CronJob) {

				// retrieve the schedule
				$schedule = $this->manager->describe($job->);


			}

		}

	}