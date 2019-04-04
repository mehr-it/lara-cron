<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 04.02.19
	 * Time: 21:04
	 */

	namespace MehrIt\LaraCron\Cron\Field;


	use DateTimeZone;
	use MehrIt\LaraCron\Cron\Field\Expression\RangeExpression;

	class HourField extends AbstractField
	{
		/**
		 * Creates a new instance
		 * @param string $expression The field expression
		 * @param DateTimeZone $timeZone The timezone to interpret the expression
		 */
		public function __construct(string $expression, DateTimeZone $timeZone) {
			parent::__construct($expression, $timeZone, 'hour', 0, 23);
		}

		/**
		 * Checks if the cron should be repeated when clock is turned back
		 * @param int $hour The hour questionable for repeat
		 * @return bool
		 */
		protected function repeatOnClockPutBack(int $hour) {

			if ($this->isWildcard())
				return true;

			foreach($this->getFieldExpressions() as $curr) {
				if ($curr instanceof RangeExpression && $curr->getMin() <= $hour && $hour < $curr->getMax())
					return true;
			}

			return false;
		}

		/**
		 * @inheritDoc
		 */
		public function matches(int $ts): bool {

			$tsDate = $this->dateForTimestamp($ts, $this->timezone);

			$extracted = $this->extractFromDate($tsDate);

			// if we have two timestamps representing the same date, but the expression indicated not repeat
			// for the given hour, we never match the later timestamp
			$timestamps = $this->timestampsForDate($tsDate, $this->timezone);
			if ($ts === ($timestamps[1] ?? null) && !$this->repeatOnClockPutBack($extracted))
				return false;


			// check if any field expression matches the extracted value
			foreach ($this->getFieldExpressions() as $currExp) {
				if ($currExp->isMatching($extracted))
					return true;
			}



			// Extracted value did not match, but maybe an alias representing the
			// same timestamp exists. Search for it...
			$aliasValue = null;
			$transitions = $this->timezone->getTransitions($ts - 3 * 3600, $ts + 3 * 3600);
			if ($transitions[1] ?? null) {

				$offset = array_shift($transitions)['offset'];

				foreach ($transitions as $currTrans) {
					$delta = $currTrans['offset'] - $offset;

					if ($delta < 0) { // put back

						if ($currTrans['ts'] <= $ts && $ts < $currTrans['ts'] - $delta) {
							// already put back => add previous timestamp
							$aliasValue = $extracted + 1;
						}
						else if ($currTrans['ts'] - $delta <= $ts && $ts < $currTrans['ts']) {
							// to be put back => add upcoming timestamp
							$aliasValue = $extracted - 1;
						}

					}
					else { // put forward
						if ($currTrans['ts'] <= $ts && $ts - $delta < $currTrans['ts']) {
							// already put back => add previous timestamp
							$aliasValue = $extracted - 1;
						}
						else if ($currTrans['ts'] - $delta <= $ts && $ts < $currTrans['ts'] - $delta) {
							// to be put back => add upcoming timestamp
							$aliasValue = $extracted + 1;
						}
					}
				}

			}

			if ($aliasValue !== null) {
				// check if any field expression matches the alias value
				foreach ($this->getFieldExpressions() as $currExp) {
					if ($currExp->isMatching($aliasValue))
						return true;
				}
			}



			return false;
		}

		/**
		 * @inheritDoc
		 */
		public function nextAfter(int $ts): int {

			// if the timestamp already matches, and another greater timestamp with equal date exists,
			// we also have another after-timestamp without advancing the next date
			if ($this->matches($ts)) {
				$tsDate = $this->dateForTimestamp($ts, $this->timezone);
				$targetFieldValue = $this->extractFromDate($tsDate);

				// we only repeat, if the current hour is meant to be repeated when clock is turned back
				if ($this->repeatOnClockPutBack($targetFieldValue)) {

					$timestamps = $this->timestampsForDate($tsDate, $this->timezone);
					if ($timestamps[0] <= $ts && ($timestamps[1] ?? null) > $ts)
						return max($this->timestampsForDate($this->startOf($tsDate, $this->field, $targetFieldValue, $this->timezone), $this->timezone));
				}
			}

			return parent::nextAfter($ts);
		}


	}