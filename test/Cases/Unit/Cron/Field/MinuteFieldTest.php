<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 28.03.19
	 * Time: 13:31
	 */

	namespace MehrItLaraCronTest\Cases\Unit\Cron\Field;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Field\MinuteField;

	class MinuteFieldTest extends FieldTest
	{
		protected function matchMinute(CronField $field, $expected, $minute) {

			$d = new Carbon();
			$d->minute($minute);

			$this->assertSame($expected, $field->matches($d->copy()->startOfMinute()->timestamp), 'Testing date ' . $d->copy()->startOfMinute()->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->second(30)->timestamp), 'Testing date ' . $d->copy()->second(30)->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->endOfMinute()->timestamp), 'Testing date ' . $d->copy()->endOfMinute()->format('Y-m-d H:i:s'));

		}

		public function testMatches_wildcard() {

			$field = new MinuteField('*', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				$this->matchMinute($field, true, $minute);
			}

		}

		public function testMatches_singleValue() {

			$field = new MinuteField('5', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch($minute) {
					case 5:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testMatches_listValue() {

			$field = new MinuteField('5,8', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch($minute) {
					case 5:
					case 8:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testMatches_range() {

			$field = new MinuteField('5-8', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch ($minute) {
					case 5:
					case 6:
					case 7:
					case 8:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testMatches_increment() {

			$field = new MinuteField('*/5', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch ($minute) {
					case 0:
					case 5:
					case 10:
					case 15:
					case 20:
					case 25:
					case 30:
					case 35:
					case 40:
					case 45:
					case 50:
					case 55:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testMatches_increment_offset() {

			$field = new MinuteField('3/11', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch ($minute) {
					case 3:
					case 14:
					case 25:
					case 36:
					case 47:
					case 58:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testMatches_increment_range() {

			$field = new MinuteField('4-8/2', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch ($minute) {
					case 4:
					case 6:
					case 8:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testMatches_complex() {

			$field = new MinuteField('9-12/2,1,3-6', $this->timezone);

			for ($minute = 0; $minute < 60; ++$minute) {
				switch ($minute) {
					case 1:
					case 3:
					case 4:
					case 5:
					case 6:
					case 9:
					case 11:
						$this->matchMinute($field, true, $minute);
						break;
					default:
						$this->matchMinute($field, false, $minute);

				}
			}

		}

		public function testNextAfter_wildcard() {

			$field = new MinuteField('*', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded     = substr("0$minute", -2);
				$nextMinutePadded = substr('0' . ($minute + 1), -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 59:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-01-01 01:$nextMinutePadded:00", $current, $field);
				}

			}

		}

		public function testNextAfter_singleValue() {

			$field = new MinuteField('5', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded = substr("0$minute", -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 01:05:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_listValue() {

			$field = new MinuteField('5,8', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded = substr("0$minute", -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 01:05:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-01-01 01:08:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_range() {

			$field = new MinuteField('5-8', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded     = substr("0$minute", -2);
				$nextMinutePadded = substr('0' . ($minute + 1), -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-01 01:05:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-01-01 01:$nextMinutePadded:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}
		}

		public function testNextAfter_increment() {

			$field = new MinuteField('*/5', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded     = substr("0$minute", -2);
				$incMinutePadded = substr('0' . ($minute - $minute % 5 + 5), -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 55:
					case 56:
					case 57:
					case 58:
					case 59:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-01-01 01:$incMinutePadded:00", $current, $field);
						break;

				}

			}
		}

		public function testNextAfter_increment_offset() {

			$field = new MinuteField('3/11', $this->timezone);

			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded     = substr("0$minute", -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 0:
					case 1:
					case 2:
						$this->assertNextTs("2019-01-01 01:03:00", $current, $field);
						break;

					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
					case 11:
					case 12:
					case 13:
						$this->assertNextTs("2019-01-01 01:14:00", $current, $field);
						break;

					case 14:
					case 15:
					case 16:
					case 17:
					case 18:
					case 19:
					case 20:
					case 21:
					case 22:
					case 23:
					case 24:
						$this->assertNextTs("2019-01-01 01:25:00", $current, $field);
						break;

					case 25:
					case 26:
					case 27:
					case 28:
					case 29:
					case 30:
					case 31:
					case 32:
					case 33:
					case 34:
					case 35:
						$this->assertNextTs("2019-01-01 01:36:00", $current, $field);
						break;

					case 36:
					case 37:
					case 38:
					case 39:
					case 40:
					case 41:
					case 42:
					case 43:
					case 44:
					case 45:
					case 46:
						$this->assertNextTs("2019-01-01 01:47:00", $current, $field);
						break;

					case 47:
					case 48:
					case 49:
					case 50:
					case 51:
					case 52:
					case 53:
					case 54:
					case 55:
					case 56:
					case 57:
						$this->assertNextTs("2019-01-01 01:58:00", $current, $field);
						break;

					case 58:
					case 59:
						$this->assertNextOverflows($current, $field);
						break;


				}

			}
		}

		public function testNextAfter_increment_range() {

			$field = new MinuteField('3-14/5', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded    = substr("0$minute", -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 0:
					case 1:
					case 2:
						$this->assertNextTs("2019-01-01 01:03:00", $current, $field);
						break;

					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-01-01 01:08:00", $current, $field);
						break;

					case 8:
					case 9:
					case 10:
					case 11:
					case 12:
						$this->assertNextTs("2019-01-01 01:13:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
						break;

				}

			}
		}

		public function testNextAfter_increment_complex() {

			$field = new MinuteField('9-12/2,1,3-6', $this->timezone);


			for ($minute = 0; $minute < 59; ++$minute) {

				$minutePadded    = substr("0$minute", -2);

				$current = "2019-01-01 01:$minutePadded:09";

				switch ($minute) {
					case 0:
						$this->assertNextTs("2019-01-01 01:01:00", $current, $field);
						break;

					case 1:
					case 2:
						$this->assertNextTs("2019-01-01 01:03:00", $current, $field);
						break;

					case 3:
						$this->assertNextTs("2019-01-01 01:04:00", $current, $field);
						break;

					case 4:
						$this->assertNextTs("2019-01-01 01:05:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-01-01 01:06:00", $current, $field);
						break;

					case 6:
					case 7:
					case 8:
						$this->assertNextTs("2019-01-01 01:09:00", $current, $field);
						break;

					case 9:
					case 10:
						$this->assertNextTs("2019-01-01 01:11:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
						break;

				}

			}
		}

		public function testMatches_DST_start() {

			$ts00 = (new Carbon('2019-03-31 01:00:00', $this->timezone))->timestamp;
			$ts05 = (new Carbon('2019-03-31 01:05:00', $this->timezone))->timestamp;

			$field00 = new MinuteField('0', $this->timezone);
			$field05 = new MinuteField('5', $this->timezone);

			$this->assertTrue($field00->matches($ts00 + 3600));
			$this->assertTrue($field00->matches($ts00 + 7200));

			$this->assertTrue($field05->matches($ts05 + 3600));
			$this->assertTrue($field05->matches($ts05 + 7200));

		}

		public function testMatches_DST_end() {

			$ts00 = (new Carbon('2019-03-31 01:00:00', $this->timezone))->timestamp;
			$ts05 = (new Carbon('2019-03-31 01:05:00', $this->timezone))->timestamp;


			$field00 = new MinuteField('0', $this->timezone);
			$field05 = new MinuteField('5', $this->timezone);

			$this->assertTrue($field00->matches($ts00 + 3600));
			$this->assertTrue($field00->matches($ts00 + 7200));

			$this->assertTrue($field05->matches($ts05 + 3600));
			$this->assertTrue($field05->matches($ts05 + 7200));

		}

		public function testNextAfter_DST_start() {

			$ts00 = (new Carbon('2019-03-31 01:00:00', $this->timezone))->timestamp;
			$ts05 = (new Carbon('2019-03-31 01:05:00', $this->timezone))->timestamp;


			$field05 = new MinuteField('5', $this->timezone);
			$field10 = new MinuteField('10', $this->timezone);


			$this->assertEquals($ts00 + 3600 + 5 * 60, $field05->nextAfter($ts00 + 3600));
			$this->assertEquals($ts00 + 7200 + 5 * 60, $field05->nextAfter($ts00 + 7200));

			$this->assertEquals($ts05 + 3600 + 5 * 60, $field10->nextAfter($ts05 + 3600));
			$this->assertEquals($ts05 + 7200 + 5 * 60, $field10->nextAfter($ts05 + 7200));



		}

		public function testNextAfter_DST_end() {

			$ts00 = (new Carbon('2019-03-31 01:00:00', $this->timezone))->timestamp;
			$ts05 = (new Carbon('2019-03-31 01:05:00', $this->timezone))->timestamp;


			$field05 = new MinuteField('5', $this->timezone);
			$field10 = new MinuteField('10', $this->timezone);

			$this->assertEquals($ts00 + 3600 + 5 * 60, $field05->nextAfter($ts00 + 3600));
			$this->assertEquals($ts00 + 7200 + 5 * 60, $field05->nextAfter($ts00 + 7200));

			$this->assertEquals($ts05 + 3600 + 5 * 60, $field10->nextAfter($ts05 + 3600));
			$this->assertEquals($ts05 + 7200 + 5 * 60, $field10->nextAfter($ts05 + 7200));
		}
	}