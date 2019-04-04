<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 05.02.19
	 * Time: 15:44
	 */

	namespace MehrIt\LaraCron\Cron;


	trait ConvertsTimestamps
	{
		/** @noinspection PhpDocMissingThrowsInspection */

		/**
		 * Gets the date for a given timestamp in the given timezone
		 * @param int $timestamp The timestamp
		 * @param \DateTimeZone $timeZone The timezone
		 * @return \DateTime The date
		 */
		protected function dateForTimestamp(int $timestamp, \DateTimeZone $timeZone) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$date = new \DateTime("@$timestamp");
			$date->setTimezone($timeZone);

			return $date;
		}


		/**
		 * Gets the timestamp(s) for a given local date. (There might exist two timestamps representing the same date and time due to DST)
		 * @param \DateTime $date The date
		 * @param \DateTimeZone $timezone The timezone
		 * @return int[] The timestamp(s), lowest first
		 */
		protected function timestampsForDate(\DateTime $date, \DateTimeZone $timezone) : array {

			// get timestamp from PHP
			$timestamp = $date->getTimestamp();

			$ret = [$timestamp];

			$transitions = $timezone->getTransitions($timestamp - 3 * 3600, $timestamp + 3 * 3600);
			if ($transitions[1] ?? null) {
				$offset = array_shift($transitions)['offset'];
				foreach ($transitions as $currTrans) {
					$delta = $currTrans['offset'] - $offset;

					if ($delta < 0) { // turn back
						if ($currTrans['ts'] <= $timestamp && $timestamp < $currTrans['ts'] - $delta) {
							// already turned back => add previous timestamp
							array_unshift($ret, $timestamp + $delta);
						}
						else if ($currTrans['ts'] - $delta <= $timestamp && $timestamp < $currTrans['ts']) {
							// to be turned back => add upcoming timestamp
							$ret[] = $timestamp - $delta;
						}
					}
				}
				return array_values($ret);

			}

			return $ret;


			// On DST end, an hour is "repeated" which means two timestamps exist
			// for the same hour. We check for a leap in time and also return the other timestamp
			if ($this->dateForTimestamp($timestamp + 3600, $timezone)->format('YmdHis') === $dateFmt)
				return [$timestamp, $timestamp + 3600];
			else if ($this->dateForTimestamp($timestamp - 3600, $timezone)->format('YmdHis') === $dateFmt)
				return [$timestamp - 3600, $timestamp];
			else
				return [$timestamp];
		}

	}