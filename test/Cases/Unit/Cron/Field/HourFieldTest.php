<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 28.03.19
	 * Time: 16:25
	 */

	namespace MehrItLaraCronTest\Cases\Unit\Cron\Field;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Field\HourField;

	class HourFieldTest extends FieldTest
	{
		protected function matchHour(CronField $field, $expected, $hour) {

			$d = new Carbon();
			$d->hour($hour);

			$this->assertSame($expected, $field->matches($d->copy()->startOfHour()->timestamp), 'Testing date ' . $d->copy()->startOfHour()->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->minute(30)->timestamp), 'Testing date ' . $d->copy()->minute(30)->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->endOfHour()->timestamp), 'Testing date ' . $d->copy()->endOfHour()->format('Y-m-d H:i:s'));

		}

		public function testMatches_wildcard() {

			$field = new HourField('*', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				$this->matchHour($field, true, $hour);
			}

		}

		public function testMatches_singleValue() {

			$field = new HourField('5', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 5:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testMatches_listValue() {

			$field = new HourField('5,8', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 5:
					case 8:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testMatches_range() {

			$field = new HourField('5-8', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 5:
					case 6:
					case 7:
					case 8:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testMatches_increment() {

			$field = new HourField('*/5', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 0:
					case 5:
					case 10:
					case 15:
					case 20:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testMatches_increment_offset() {

			$field = new HourField('3/5', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 3:
					case 8:
					case 13:
					case 18:
					case 23:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testMatches_increment_range() {

			$field = new HourField('4-8/2', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 4:
					case 6:
					case 8:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testMatches_complex() {

			$field = new HourField('9-12/2,1,3-6', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {
				switch ($hour) {
					case 1:
					case 3:
					case 4:
					case 5:
					case 6:
					case 9:
					case 11:
						$this->matchHour($field, true, $hour);
						break;
					default:
						$this->matchHour($field, false, $hour);

				}
			}

		}

		public function testNextAfter_wildcard() {

			$field = new HourField('*', $this->timezone);


			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded    = substr("0$hour", -2);
				$nexHourPadded = substr('0' . ($hour + 1), -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch($hour) {
					case 23:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-01-01 $nexHourPadded:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_singleValue() {

			$field = new HourField('5', $this->timezone);


			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded    = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 05:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_listValue() {

			$field = new HourField('5,8', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 05:00:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-01-01 08:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_rangeValue() {

			$field = new HourField('5-8', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 05:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-01-01 06:00:00", $current, $field);
						break;

					case 6:
						$this->assertNextTs("2019-01-01 07:00:00", $current, $field);
						break;

					case 7:
						$this->assertNextTs("2019-01-01 08:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}
		}

		public function testNextAfter_increment() {

			$field = new HourField('*/5', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 05:00:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
						$this->assertNextTs("2019-01-01 10:00:00", $current, $field);
						break;

					case 10:
					case 11:
					case 12:
					case 13:
					case 14:
						$this->assertNextTs("2019-01-01 15:00:00", $current, $field);
						break;

					case 15:
					case 16:
					case 17:
					case 18:
					case 19:
						$this->assertNextTs("2019-01-01 20:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}
		}

		public function testNextAfter_increment_offset() {

			$field = new HourField('3/8', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
					case 1:
					case 2:
						$this->assertNextTs("2019-01-01 03:00:00", $current, $field);
						break;

					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
						$this->assertNextTs("2019-01-01 11:00:00", $current, $field);
						break;

					case 11:
					case 12:
					case 13:
					case 14:
					case 15:
					case 16:
					case 17:
					case 18:
						$this->assertNextTs("2019-01-01 19:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}
		}

		public function testNextAfter_increment_range() {

			$field = new HourField('4-8/2', $this->timezone);

			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
					case 1:
					case 2:
					case 3:
						$this->assertNextTs("2019-01-01 04:00:00", $current, $field);
						break;

					case 4:
					case 5:
						$this->assertNextTs("2019-01-01 06:00:00", $current, $field);
						break;

					case 6:
					case 7:
						$this->assertNextTs("2019-01-01 08:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}
		}

		public function testNextAfter_complex() {

			$field = new HourField('9-12/2,1,3-6', $this->timezone);;

			for ($hour = 0; $hour < 24; ++$hour) {

				$hourPadded = substr("0$hour", -2);

				$current = "2019-01-01 $hourPadded:45:09";

				switch ($hour) {
					case 0:
						$this->assertNextTs("2019-01-01 01:00:00", $current, $field);
						break;

					case 1:
					case 2:
						$this->assertNextTs("2019-01-01 03:00:00", $current, $field);
						break;

					case 3:
						$this->assertNextTs("2019-01-01 04:00:00", $current, $field);
						break;

					case 4:
						$this->assertNextTs("2019-01-01 05:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-01-01 06:00:00", $current, $field);
						break;

					case 6:
					case 7:
					case 8:
						$this->assertNextTs("2019-01-01 09:00:00", $current, $field);
						break;

					case 9:
					case 10:
						$this->assertNextTs("2019-01-01 11:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}
		}

		public function testMatches_DST_start() {

			$ts = (new Carbon('2019-03-31 01:00:00', $this->timezone))->timestamp;

			$field2000 = new HourField('2', $this->timezone);
			$field3000 = new HourField('3', $this->timezone);

			$this->assertTrue($field2000->matches($ts + 3600));
			$this->assertTrue($field3000->matches($ts + 3600));

		}

		public function testMatches_DST_end() {

			$ts = (new Carbon('2019-10-27 01:00:00', $this->timezone))->timestamp;

			$field = new HourField('2', $this->timezone);
			$this->assertTrue($field->matches($ts + 3600));
			$this->assertFalse($field->matches($ts + 7200));
			$this->assertFalse($field->matches($ts + 10800));

			$field = new HourField('3', $this->timezone);
			$this->assertFalse($field->matches($ts + 3600));
			$this->assertFalse($field->matches($ts + 7200));
			$this->assertTrue($field->matches($ts + 10800));

			$field = new HourField('2,3', $this->timezone);
			$this->assertTrue($field->matches($ts + 3600));
			$this->assertFalse($field->matches($ts + 7200));
			$this->assertTrue($field->matches($ts + 10800));

			$field = new HourField('2-3', $this->timezone);
			$this->assertTrue($field->matches($ts + 3600));
			$this->assertTrue($field->matches($ts + 7200));
			$this->assertTrue($field->matches($ts + 10800));

			$field = new HourField('3-4', $this->timezone);
			$this->assertFalse($field->matches($ts + 3600));
			$this->assertFalse($field->matches($ts + 7200));
			$this->assertTrue($field->matches($ts + 10800));

		}

		public function testNextAfter_DST_start() {

			$ts = (new Carbon('2019-03-31 01:00:00', $this->timezone))->timestamp;

			$field = new HourField('2', $this->timezone);
			$this->assertEquals($ts + 3600, $field->nextAfter($ts));


			$field = new HourField('2,3,4', $this->timezone);
			$this->assertEquals($ts + 7200, $field->nextAfter($ts + 3600));


		}

		public function testNextAfter_DST_end() {

			$ts = (new Carbon('2019-10-27 01:00:00', $this->timezone))->timestamp;

			// skip second timestamp for date
			$field = new HourField('2,3', $this->timezone);
			$this->assertEquals($ts + 3600, $field->nextAfter($ts));
			$this->assertEquals($ts + 10800, $field->nextAfter($ts + 3800));

			// match second timestamp
			$field = new HourField('2-3', $this->timezone);
			$this->assertEquals($ts + 3600, $field->nextAfter($ts));
			$this->assertEquals($ts + 7200, $field->nextAfter($ts + 3600));

			// match second timestamp
			$field = new HourField('1-9', $this->timezone);
			$this->assertEquals($ts + 3600, $field->nextAfter($ts));
			$this->assertEquals($ts + 7200, $field->nextAfter($ts + 3600));

			// match second timestamp
			$field = new HourField('*', $this->timezone);
			$this->assertEquals($ts + 3600, $field->nextAfter($ts));
			$this->assertEquals($ts + 7200, $field->nextAfter($ts + 3600));

			// skip second timestamp for date
			$field = new HourField('0-2,3', $this->timezone);
			$this->assertEquals($ts + 3600, $field->nextAfter($ts));
			$this->assertEquals($ts + 10800, $field->nextAfter($ts + 3800));
		}

	}