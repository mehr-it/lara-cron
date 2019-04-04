<?php /** @noinspection PhpUnhandledExceptionInspection */

	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 09.02.19
	 * Time: 00:10
	 */

	namespace MehrItLaraCronTest\Cases\Unit\Cron\Field;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Field\MonthField;

	class MonthFieldTest extends FieldTest
	{
		protected function matchMonth(CronField $field, $expected, $month) {

			$d = new Carbon();
			$d->day(1);
			$d->month($month);

			$this->assertSame($expected, $field->matches($d->copy()->startOfMonth()->timestamp), 'Testing date ' . $d->copy()->startOfMonth()->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->day(15)->timestamp), 'Testing date ' . $d->copy()->day(15)->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->endOfMonth()->timestamp), 'Testing date ' . $d->copy()->endOfMonth()->format('Y-m-d H:i:s'));

		}


		public function testMatches_wildcard() {

			$field = new MonthField('*', $this->timezone);

			$this->matchMonth($field,true, 1);
			$this->matchMonth($field,true, 2);
			$this->matchMonth($field,true, 3);
			$this->matchMonth($field,true, 4);
			$this->matchMonth($field,true, 5);
			$this->matchMonth($field,true, 6);
			$this->matchMonth($field,true, 7);
			$this->matchMonth($field,true, 8);
			$this->matchMonth($field,true, 9);
			$this->matchMonth($field,true, 10);
			$this->matchMonth($field,true, 11);
			$this->matchMonth($field,true, 12);

		}

		public function testMatches_allMonthNames() {

			$field = new MonthField('JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOV,DEC', $this->timezone);

			$this->matchMonth($field,true, 1);
			$this->matchMonth($field,true, 2);
			$this->matchMonth($field,true, 3);
			$this->matchMonth($field,true, 4);
			$this->matchMonth($field,true, 5);
			$this->matchMonth($field,true, 6);
			$this->matchMonth($field,true, 7);
			$this->matchMonth($field,true, 8);
			$this->matchMonth($field,true, 9);
			$this->matchMonth($field,true, 10);
			$this->matchMonth($field,true, 11);
			$this->matchMonth($field,true, 12);

		}

		public function testMatches_singleValue() {

			$field = new MonthField('5', $this->timezone);

			$this->matchMonth($field,false, 1);
			$this->matchMonth($field,false, 2);
			$this->matchMonth($field,false, 3);
			$this->matchMonth($field,false, 4);
			$this->matchMonth($field,true, 5);
			$this->matchMonth($field,false, 6);
			$this->matchMonth($field,false, 7);
			$this->matchMonth($field,false, 8);
			$this->matchMonth($field,false, 9);
			$this->matchMonth($field,false, 10);
			$this->matchMonth($field,false, 11);
			$this->matchMonth($field,false, 12);

		}

		public function testMatches_singleValue_named() {

			$field = new MonthField('MAY', $this->timezone);

			$this->matchMonth($field,false, 1);
			$this->matchMonth($field,false, 2);
			$this->matchMonth($field,false, 3);
			$this->matchMonth($field,false, 4);
			$this->matchMonth($field,true, 5);
			$this->matchMonth($field,false, 6);
			$this->matchMonth($field,false, 7);
			$this->matchMonth($field,false, 8);
			$this->matchMonth($field,false, 9);
			$this->matchMonth($field,false, 10);
			$this->matchMonth($field,false, 11);
			$this->matchMonth($field,false, 12);

		}

		public function testMatches_list() {

			$field = new MonthField('5,8', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, false, 4);
			$this->matchMonth($field, true, 5);
			$this->matchMonth($field, false, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, true, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_list_named() {

			$field = new MonthField('MAY,AUG', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, false, 4);
			$this->matchMonth($field, true, 5);
			$this->matchMonth($field, false, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, true, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_range() {

			$field = new MonthField('5-8', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, false, 4);
			$this->matchMonth($field, true, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, true, 7);
			$this->matchMonth($field, true, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_range_named() {

			$field = new MonthField('MAY-AUG', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, false, 4);
			$this->matchMonth($field, true, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, true, 7);
			$this->matchMonth($field, true, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_increment() {

			$field = new MonthField('*/5', $this->timezone);

			$this->matchMonth($field, true, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, false, 4);
			$this->matchMonth($field, false, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, false, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, true, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_increment_offset() {

			$field = new MonthField('4/3', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, true, 4);
			$this->matchMonth($field, false, 5);
			$this->matchMonth($field, false, 6);
			$this->matchMonth($field, true, 7);
			$this->matchMonth($field, false, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, true, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_increment_offset_named() {

			$field = new MonthField('APR/3', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, true, 4);
			$this->matchMonth($field, false, 5);
			$this->matchMonth($field, false, 6);
			$this->matchMonth($field, true, 7);
			$this->matchMonth($field, false, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, true, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_increment_range() {

			$field = new MonthField('4-8/2', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, true, 4);
			$this->matchMonth($field, false, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, true, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_increment_range_named() {

			$field = new MonthField('APR-AUG/2', $this->timezone);

			$this->matchMonth($field, false, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, false, 3);
			$this->matchMonth($field, true, 4);
			$this->matchMonth($field, false, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, true, 8);
			$this->matchMonth($field, false, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, false, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_complex() {

			$field = new MonthField('9-12/2,1,3-6', $this->timezone);

			$this->matchMonth($field, true, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, true, 3);
			$this->matchMonth($field, true, 4);
			$this->matchMonth($field, true, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, false, 8);
			$this->matchMonth($field, true, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, true, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testMatches_complex_named() {

			$field = new MonthField('SEP-DEC/2,JAN,MAR-JUN', $this->timezone);

			$this->matchMonth($field, true, 1);
			$this->matchMonth($field, false, 2);
			$this->matchMonth($field, true, 3);
			$this->matchMonth($field, true, 4);
			$this->matchMonth($field, true, 5);
			$this->matchMonth($field, true, 6);
			$this->matchMonth($field, false, 7);
			$this->matchMonth($field, false, 8);
			$this->matchMonth($field, true, 9);
			$this->matchMonth($field, false, 10);
			$this->matchMonth($field, true, 11);
			$this->matchMonth($field, false, 12);

		}

		public function testNextAfter_wildcard() {

			$field = new MonthField('*', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);
				$nextMonthPadded = substr('0' . ($month + 1), -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 12:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-$nextMonthPadded-01 00:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_allMonthNames() {

			$field = new MonthField('JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOV,DEC', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);
				$nextMonthPadded = substr('0' . ($month + 1), -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 12:
						$this->assertNextOverflows($current, $field);
						break;

					default:
						$this->assertNextTs("2019-$nextMonthPadded-01 00:00:00", $current, $field);
				}

			}

		}

		public function testNextAfter_singleValue() {

			$field = new MonthField('5', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_singleValue_named() {

			$field = new MonthField('MAY', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_list() {

			$field = new MonthField('5,8', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-08-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_list_named() {

			$field = new MonthField('MAY,AUG', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					case 5:
					case 6:
					case 7:
						$this->assertNextTs("2019-08-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_range() {

			$field = new MonthField('5-8', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;
					case 6:
						$this->assertNextTs("2019-07-01 00:00:00", $current, $field);
						break;
					case 7:
						$this->assertNextTs("2019-08-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_range_named() {

			$field = new MonthField('MAY-AUG', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;
					case 6:
						$this->assertNextTs("2019-07-01 00:00:00", $current, $field);
						break;
					case 7:
						$this->assertNextTs("2019-08-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_increment() {

			$field = new MonthField('*/5', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
						$this->assertNextTs("2019-11-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_offset() {

			$field = new MonthField('4/3', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
						$this->assertNextTs("2019-04-01 00:00:00", $current, $field);
						break;

					case 4:
					case 5:
					case 6:
						$this->assertNextTs("2019-07-01 00:00:00", $current, $field);
						break;

					case 7:
					case 8:
					case 9:
						$this->assertNextTs("2019-10-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_offset_named() {

			$field = new MonthField('APR/3', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
						$this->assertNextTs("2019-04-01 00:00:00", $current, $field);
						break;

					case 4:
					case 5:
					case 6:
						$this->assertNextTs("2019-07-01 00:00:00", $current, $field);
						break;

					case 7:
					case 8:
					case 9:
						$this->assertNextTs("2019-10-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_offset_range() {

			$field = new MonthField('4-8/2', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
						$this->assertNextTs("2019-04-01 00:00:00", $current, $field);
						break;

					case 4:
					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
						$this->assertNextTs("2019-08-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_offset_range_named() {

			$field = new MonthField('APR-AUG/2', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
					case 3:
						$this->assertNextTs("2019-04-01 00:00:00", $current, $field);
						break;

					case 4:
					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
						$this->assertNextTs("2019-08-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_complex() {

			$field = new MonthField('9-12/2,1,3-6', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
						$this->assertNextTs("2019-03-01 00:00:00", $current, $field);
						break;

					case 3:
						$this->assertNextTs("2019-04-01 00:00:00", $current, $field);
						break;

					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
					case 8:
						$this->assertNextTs("2019-09-01 00:00:00", $current, $field);
						break;

					case 9:
					case 10:
						$this->assertNextTs("2019-11-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}

		public function testNextAfter_complex_named() {

			$field = new MonthField('SEP-DEC/2,JAN,MAR-JUN', $this->timezone);


			for ($month = 1; $month < 13; ++$month) {

				$monthPadded     = substr("0$month", -2);

				$current = "2019-$monthPadded-01 00:00:00";

				switch ($month) {
					case 1:
					case 2:
						$this->assertNextTs("2019-03-01 00:00:00", $current, $field);
						break;

					case 3:
						$this->assertNextTs("2019-04-01 00:00:00", $current, $field);
						break;

					case 4:
						$this->assertNextTs("2019-05-01 00:00:00", $current, $field);
						break;

					case 5:
						$this->assertNextTs("2019-06-01 00:00:00", $current, $field);
						break;

					case 6:
					case 7:
					case 8:
						$this->assertNextTs("2019-09-01 00:00:00", $current, $field);
						break;

					case 9:
					case 10:
						$this->assertNextTs("2019-11-01 00:00:00", $current, $field);
						break;

					default:
						$this->assertNextOverflows($current, $field);
				}

			}

		}




	}