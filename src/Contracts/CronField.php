<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.02.19
	 * Time: 23:43
	 */

	namespace MehrIt\LaraCron\Contracts;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;

	interface CronField
	{
		/**
		 * Returns if the field expression matches the timestamp
		 * @param int $ts The timestamp
		 * @return bool True if matching. Else false.
		 */
		public function matches(int $ts): bool;

		/**
		 * Returns the next matching timestamp after the given date
		 * @param int $ts The timestamp
		 * @return int The next timestamp
		 * @throws OutOfRangeException
		 */
		public function nextAfter(int $ts): int;

		/**
		 * Checks whether the given field holds a wildcard expression and therefore will match any valid values
		 * @return bool True if wildcard. Else false.
		 */
		public function isWildcard() : bool;

	}