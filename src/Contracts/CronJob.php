<?php


	namespace MehrIt\LaraCron\Contracts;

	use Illuminate\Contracts\Queue\ShouldQueue;

	interface CronJob extends ShouldQueue
	{

		/**
		 * Set the desired delay for the job.
		 *
		 * @param \DateTime|int|null $delay
		 * @return $this
		 */
		public function delay($delay);
	}