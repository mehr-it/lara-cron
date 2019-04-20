<?php


	namespace MehrIt\LaraCron\Contracts;

	interface CronJob
	{

		/**
		 * Set the desired delay for the job.
		 *
		 * @param \DateTime|int|null $delay
		 * @return $this
		 */
		public function delay($delay);
	}