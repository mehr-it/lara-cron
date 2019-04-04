<?php


	namespace MehrIt\LaraCron\Cron;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;

	trait DateStartOf
	{
		/**
		 * Make the next matching date
		 * @param \DateTime $date The date
		 * @param string $field The component, one of "Y", "m", "d", "H", "i", "s"
		 * @param int $fieldValue The component value
		 * @param \DateTimeZone $timezone The timezone
		 * @return \DateTime The date
		 * @throws OutOfRangeException
		 */
		protected function startOf(\DateTime $date, string $field, int $fieldValue, \DateTimeZone $timezone) {

			// if we are setting the year field, we simply create a new date
			if ($field == 'year') {
				/** @noinspection PhpUnhandledExceptionInspection */
				return new \DateTime("{$fieldValue}-01-01 00:00:00", $timezone);
			}

			// prefix with zero
			if ($fieldValue < 10)
				$fieldValue = '0' . $fieldValue;

			// select format which keeps all superior field from original date,
			// injects the value for the given field and resets all minor
			// fields
			switch ($field) {
				case 'month':
					$fmt = "Y-{$fieldValue}-01 00:00:00";
					break;

				case 'day':
					// check not to set an invalid day for the given month
					if ($fieldValue > $date->format('t'))
						throw new OutOfRangeException();

					$fmt = "Y-m-{$fieldValue} 00:00:00";
					break;

				case 'hour':

					$fmt = "Y-m-d {$fieldValue}:00:00";
					break;

				case 'minute':
					$fmt = "Y-m-d H:{$fieldValue}:00";
					break;

				case 'second':
					$fmt = "Y-m-d H:i:{$fieldValue}";
					break;

				default:
					throw new \InvalidArgumentException("Field \"$field\" is invalid");
			}

			/** @noinspection PhpUnhandledExceptionInspection */
			return new \DateTime($date->format($fmt), $timezone);
		}
	}