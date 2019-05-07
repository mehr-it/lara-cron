<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 29.03.19
	 * Time: 10:42
	 */

	namespace MehrIt\LaraCron\Contracts;

	use DateTimeZone;

	interface CronExpression
	{
		/**
		 * Creates a new instance
		 * @param string $expression The cron expression
		 * @param string|DateTimeZone $timezone The timezone to interpret the expression
		 */
		public function __construct(string $expression, $timezone);

		/**
		 * Returns if the cron expression matches the timestamp
		 * @param int $ts The timestamp
		 * @return bool True if matching. Else false.
		 */
		public function matches(int $ts): bool;

		/**
		 * Returns the next matching timestamp after the given date
		 * @param int $ts The timestamp
		 * @param int $maxTs The maximum timestamp to return
		 * @return null|int The next timestamp
		 */
		public function nextAfter(int $ts, int $maxTs): ?int;


		/**
		 * Gets the underlying expression string
		 * @return string The underlying expression string
		 */
		public function getExpression() : string;

		/**
		 * Gets the timezone the expression has to be interpreted in
		 * @return DateTimeZone The timezone
		 */
		public function getTimezone() : DateTimeZone;
	}