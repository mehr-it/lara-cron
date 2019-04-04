<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 28.03.19
	 * Time: 16:52
	 */

	namespace MehrItLaraCronTest\Cases\Unit\Cron\Field;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Field\DayOfMonthField;

	class DayOfMonthFieldTest extends FieldTest
	{
		protected function matchDayOfMonth(CronField $field, $expected, $dom) {

			$d = new Carbon('2019-01-01 00:00:00');
			$d->day($dom);

			$this->assertSame($expected, $field->matches($d->copy()->startOfDay()->timestamp), 'Testing date ' . $d->copy()->startOfDay()->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->hour(12)->timestamp), 'Testing date ' . $d->copy()->hour(12)->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->endOfDay()->timestamp), 'Testing date ' . $d->copy()->endOfDay()->format('Y-m-d H:i:s'));

		}

		public function testMatches_wildcard() {

			$field = new DayOfMonthField('*', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				$this->matchDayOfMonth($field, true, $dom);
			}

		}

		public function testMatches_singleValue() {

			$field = new DayOfMonthField('5', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 5:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testMatches_listValue() {

			$field = new DayOfMonthField('5,8', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 5:
					case 8:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testMatches_range() {

			$field = new DayOfMonthField('5-8', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 5:
					case 6:
					case 7:
					case 8:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testMatches_increment() {

			$field = new DayOfMonthField('*/5', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 1:
					case 6:
					case 11:
					case 16:
					case 21:
					case 26:
					case 31:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testMatches_increment_offset() {

			$field = new DayOfMonthField('3/8', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 3:
					case 11:
					case 19:
					case 27:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testMatches_increment_range() {

			$field = new DayOfMonthField('4-8/2', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 4:
					case 6:
					case 8:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testMatches_complex() {

			$field = new DayOfMonthField('9-12/2,1,3-6', $this->timezone);

			for ($dom = 1; $dom < 32; ++$dom) {
				switch ($dom) {
					case 1:
					case 3:
					case 4:
					case 5:
					case 6:
					case 9:
					case 11:
						$this->matchDayOfMonth($field, true, $dom);
						break;
					default:
						$this->matchDayOfMonth($field, false, $dom);

				}
			}

		}

		public function testNextAfter_wildcard() {

			$field = new DayOfMonthField('*', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);
				$nextDomPadded = substr('0' . ($dom + 1), -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 31:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-01-$nextDomPadded 00:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_wildcard_30days() {

			$field = new DayOfMonthField('*', $this->timezone);


			for ($dom = 1; $dom < 31; ++$dom) {

				$domPadded     = substr("0$dom", -2);
				$nextDomPadded = substr('0' . ($dom + 1), -2);

				$current = "2019-04-$domPadded 00:00:00";

				switch ($dom) {
					case 30:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-04-$nextDomPadded 00:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_wildcard_28days() {

			$field = new DayOfMonthField('*', $this->timezone);


			for ($dom = 1; $dom < 29; ++$dom) {

				$domPadded     = substr("0$dom", -2);
				$nextDomPadded = substr('0' . ($dom + 1), -2);

				$current = "2019-02-$domPadded 00:00:00";

				switch ($dom) {
					case 28:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-02-$nextDomPadded 00:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_wildcard_29days() {

			$field = new DayOfMonthField('*', $this->timezone);


			for ($dom = 1; $dom < 30; ++$dom) {

				$domPadded     = substr("0$dom", -2);
				$nextDomPadded = substr('0' . ($dom + 1), -2);

				$current = "2020-02-$domPadded 00:00:00";

				switch ($dom) {
					case 29:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2020-02-$nextDomPadded 00:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_singleValue() {

			$field = new DayOfMonthField('5', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-05 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_listValue() {

			$field = new DayOfMonthField('5,8', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-05 00:00:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-01-08 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_range() {

			$field = new DayOfMonthField('5-8', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-01-05 00:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-01-06 00:00:00", $current, $field);
						break;

					case 6:
						$this->assertNextTs("2019-01-07 00:00:00", $current, $field);
						break;

					case 7:
						$this->assertNextTs("2019-01-08 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_increment() {

			$field = new DayOfMonthField('*/11', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
					case 11:
						$this->assertNextTs("2019-01-12 00:00:00", $current, $field);
						break;

					case 12:
					case 13:
					case 14:
					case 15:
					case 16:
					case 17:
					case 18:
					case 19:
					case 20:
					case 21:
					case 22:
						$this->assertNextTs("2019-01-23 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_increment_offset() {

			$field = new DayOfMonthField('3/11', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
						$this->assertNextTs("2019-01-03 00:00:00", $current, $field);
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
						$this->assertNextTs("2019-01-14 00:00:00", $current, $field);
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
						$this->assertNextTs("2019-01-25 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_increment_range() {

			$field = new DayOfMonthField('4-8/2', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
					case 3:
						$this->assertNextTs("2019-01-04 00:00:00", $current, $field);
						break;

					case 4:
					case 5:
						$this->assertNextTs("2019-01-06 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
						$this->assertNextTs("2019-01-08 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}



		}public function testNextAfter_complex() {

			$field = new DayOfMonthField('9-12/2,1,3-6', $this->timezone);


			for ($dom = 1; $dom < 32; ++$dom) {

				$domPadded     = substr("0$dom", -2);

				$current = "2019-01-$domPadded 00:00:00";

				switch ($dom) {
					case 1:
					case 2:
						$this->assertNextTs("2019-01-03 00:00:00", $current, $field);
						break;

					case 3:
						$this->assertNextTs("2019-01-04 00:00:00", $current, $field);
						break;

					case 4:
						$this->assertNextTs("2019-01-05 00:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-01-06 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
					case 8:
						$this->assertNextTs("2019-01-09 00:00:00", $current, $field);
						break;

					case 9:
					case 10:
						$this->assertNextTs("2019-01-11 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}
	}