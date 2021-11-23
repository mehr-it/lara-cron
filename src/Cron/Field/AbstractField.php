<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.02.19
	 * Time: 23:45
	 */

	namespace MehrIt\LaraCron\Cron\Field;


	use DateTime;
	use DateTimeZone;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\DateStartOf;
	use MehrIt\LaraCron\Cron\Exception\InvalidCronFieldExpressionException;
	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;
	use MehrIt\LaraCron\Cron\ConvertsTimestamps;
	use MehrIt\LaraCron\Cron\Field\Expression\FieldExpression;
	use MehrIt\LaraCron\Cron\Field\Expression\IncrementExpression;
	use MehrIt\LaraCron\Cron\Field\Expression\RangeExpression;
	use MehrIt\LaraCron\Cron\Field\Expression\ValueExpression;
	use MehrIt\LaraCron\Cron\Field\Expression\WildcardExpression;



	abstract class AbstractField implements CronField
	{
		use ConvertsTimestamps;
		use DateStartOf;


		const PARSERS = [
			'/' => 'parseIncrement',
			'-' => 'parseRange',
		];

		const FIELD_FORMATS = [
			'year'      => 'Y',
			'month'     => 'm',
			'day'       => 'd',
			'dayOfWeek' => 'w',
			'hour'      => 'H',
			'minute'    => 'i',
			'second'    => 's',
		];

		const DEFAULT_PARSER = 'parseValue';

		protected $additionalParsers = [];


		protected $fieldExpressions;


		protected $min;
		protected $max;
		protected $matchingValues;
		protected $field;
		protected $expression;
		protected $timezone;

		/**
		 * Creates a new instance
		 * @param string $expression The expression
		 * @param DateTimeZone $timeZone The timezone to interpret the expression
		 * @param string $field The field
		 * @param int $min The minimum field value
		 * @param int $max The maximum field value
		 */
		public function __construct(string $expression, DateTimeZone $timeZone, string $field, ?int $min, ?int $max) {
			$this->field         = $field;
			$this->min           = $min;
			$this->max           = $max;
			$this->expression    = $expression;
			$this->timezone      = $timeZone;
		}

		/**
		 * @return FieldExpression[]
		 */
		protected function getFieldExpressions() : array {

			if (!$this->fieldExpressions)
				$this->fieldExpressions = $this->parse($this->expression);

			return $this->fieldExpressions;
		}

		/**
		 * Parses the given expression
		 * @param string $expression The expression
		 * @throws InvalidCronFieldExpressionException
		 * @return FieldExpression[] The parsed expressions
		 */
		protected function parse(string $expression) {

			// handle wildcards
			if ($expression === '*')
				return [new WildcardExpression($this->min, $this->max)];


			$ret = [];

			// build parsers array
			$parsers = array_merge(static::PARSERS, $this->additionalParsers);

			// parse all expressions
			$expressions = explode(',', $expression);
			foreach($expressions as $exp) {

				// iterate parsers and use first one which's pattern matches
				$parsed = false;
				foreach($parsers as $pattern => $parserMethod) {
					if (strpos($exp, $pattern) !== false) {
						$ret[] = $this->{$parserMethod}($exp);
						$parsed = true;
						break;
					}
				}

				// if yet not parsed, parse using default parser function
				if (!$parsed)
					$ret[] = $this->{static::DEFAULT_PARSER}($exp);
			}

			return $ret;
		}

		protected function parseRange(string $expression) : FieldExpression {
			$exp = explode('-', $expression, 2);

			if (count($exp) !== 2)
				throw new InvalidCronFieldExpressionException($this->expression, $this->field);

			$from = $this->parseToken($exp[0]);
			$to   = $this->parseToken($exp[1]);

			if ($from >= $to)
				throw new InvalidCronFieldExpressionException($this->expression, $this->field);

			return new RangeExpression($from, $to);
		}

		protected function parseIncrement(string $expression) : FieldExpression {
			// range increment => split and parse
			$incSplit = explode('/', $expression, 2);
			if (count($incSplit) !== 2)
				throw new InvalidCronFieldExpressionException($this->expression, $this->field);

			if ($incSplit[0] == '*') {
				$from = $this->min;
				$to   = $this->max;
			}
			else {
				$between = explode('-', $incSplit[0], 2);
				switch (count($between)) {
					case 1:
						$from = $this->parseToken($between[0]);
						$to   = $this->max;
						break;
					case 2:
						$from = $this->parseToken($between[0]);
						$to   = $this->parseToken($between[1]);
						break;
					default:
						throw new InvalidCronFieldExpressionException($this->expression, $this->field);
				}


			}

			$inc = (int)$incSplit[1];
			if (!$this->isInBounds($inc) || $inc == 0)
				throw new InvalidCronFieldExpressionException($this->expression, $this->field);

			return new IncrementExpression($inc, $from, $to);
		}

		/**
		 * Parses the given value expression
		 * @param string $expression The given expression
		 * @throws InvalidCronFieldExpressionException
		 * @return FieldExpression The parsed expression
		 */
		protected function parseValue(string $expression) : FieldExpression {

			return new ValueExpression($this->parseToken($expression));

		}

		/**
		 * Parses the given token which must be in bounds of the field min and max values
		 * @param string $expression The expression
		 * @return int The number
		 */
		protected function parseToken(string $expression) {
			
			if (trim($expression) === '' || !$this->isInBounds($expression))
				throw new InvalidCronFieldExpressionException($this->expression, $this->field);

			return (int)$expression;
		}


		/**
		 * Checks if the given value is a valid integer in bounds
		 * @param string $value The value
		 * @return bool True if in bounds. Else false.
		 */
		protected function isInBounds($value) {

			return
				((int)$value == $value) && $value >= 0 && // must be positive integer
				($this->min === null || $value >= $this->min) && // must match minimum if set
				($this->max === null || $value <= $this->max); // must match maximum if set
		}


		/**
		 * Returns the next matching date after given timestamp
		 * @param int $ts The timestamp
		 * @return DateTime The next matching date
		 * @throws OutOfRangeException
		 */
		protected function nextMatchingDate(int $ts) : DateTime {

			$targetDate = $this->dateForTimestamp($ts, $this->timezone);
			$targetFieldValue = $this->extractFromDate($targetDate);

			// If we multiple timestamps exist for the given date, and one is passed while
			// the other one is not passed, we do not advance to next field value. This allows
			// the caller to receive the same matching date again if multiple timestamps
			// are labeled with the same date
//			if ($this->field == 'hour') {
//				$tsForDate = $this->timestampsForDate($targetDate, $this->timezone);
//				if ($tsForDate[0] <= $ts && ($tsForDate[1] ?? null) > $ts)
//					return $this->startOf($targetDate, $this->field, $targetFieldValue, $this->timezone);
//			}

			$leastLaterValue = null;

			// here we determine the next matching value for the field
			foreach ($this->getFieldExpressions() as $currExpr) {

				try {
					$exprNext = $currExpr->nextAfter($targetFieldValue);
					if ($exprNext < $leastLaterValue || $leastLaterValue === null)
						$leastLaterValue = $exprNext;
				}
				catch (OutOfRangeException $ex) {

				}

			}

			// if no later matching value can be found, we throw an exception
			if ($leastLaterValue === null)
				throw new OutOfRangeException();

			return $this->startOf($targetDate, $this->field, $leastLaterValue, $this->timezone);
		}


		/**
		 * Extracts the field value from given date
		 * @param DateTime $date The date
		 * @return int The field value
		 */
		protected function extractFromDate(DateTime $date) {

			return (int)$date->format(self::FIELD_FORMATS[$this->field]);
		}/** @noinspection PhpDocMissingThrowsInspection */

		/**
		 * @inheritDoc
		 */
		public function matches(int $ts): bool {

			$fieldValue = $this->extractFromDate($this->dateForTimestamp($ts, $this->timezone));

			foreach ($this->getFieldExpressions() as $currExp) {
				if ($currExp->isMatching($fieldValue))
					return true;
			}

			return false;
		}

		/**
		 * @inheritDoc
		 */
		public function nextAfter(int $ts): int {

			// get the next matching date
			$date = $this->nextMatchingDate($ts);

			// get the timestamp(s) for the next matching date
			$timestamps = $this->timestampsForDate($date, $this->timezone);


			// We return the first (lowest) timestamp if it is greater than the
			// passed one. If the first one is lower, we have an ambiguous date
			// and have to decide if we return the second timestamp for the same
			// date or search the next timestamp after the later timestamp.
			if ($timestamps[0] > $ts) {
				return $timestamps[0];
			}
			else {
				// We are about to return another timestamp for a date which already
				// matched an earlier timestamp. We only do this, if later timestamps
				// for same dates should not be skipped

				return $timestamps[1];
			}

		}

		/**
		 * @inheritDoc
		 */
		public function isWildcard(): bool {
			return $this->expression === '*';
		}

	}