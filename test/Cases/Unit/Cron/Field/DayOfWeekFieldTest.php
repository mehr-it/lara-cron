<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 28.03.19
	 * Time: 17:13
	 */

	namespace MehrItLaraCronTest\Cases\Unit\Cron\Field;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Field\DayOfWeekField;

	class DayOfWeekFieldTest extends FieldTest
	{
		protected function matchDayOfWeek(CronField $field, $expected, $dow) {

			$d = new Carbon('2019-03-25 00:00:00');
			switch($dow) {
				case 0:
					$d->addDays(6);
					break;

				default:
					$d->addDays($dow - 1);

			}

			$this->assertSame($expected, $field->matches($d->copy()->startOfDay()->timestamp), 'Testing date ' . $d->copy()->startOfDay()->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->hour(12)->timestamp), 'Testing date ' . $d->copy()->hour(12)->format('Y-m-d H:i:s'));
			$this->assertSame($expected, $field->matches($d->copy()->endOfDay()->timestamp), 'Testing date ' . $d->copy()->endOfDay()->format('Y-m-d H:i:s'));

		}


		public function testMatches_wildcard() {

			$field = new DayOfWeekField('*', $this->timezone);

			$this->matchDayOfWeek($field, true, 0);
			$this->matchDayOfWeek($field, true, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, true, 6);

		}

		public function testMatches_0matchesSunday() {

			$field = new DayOfWeekField('0', $this->timezone);
			$this->matchDayOfWeek($field, true, 0);

		}

		public function testMatches_7matchesSunday() {

			$field = new DayOfWeekField('7', $this->timezone);
			$this->matchDayOfWeek($field, true, 0);

		}

		public function testMatches_allDayNames() {

			$field = new DayOfWeekField('MON,TUE,WED,THU,FRI,SAT,SUN', $this->timezone);

			$this->matchDayOfWeek($field, true, 0);
			$this->matchDayOfWeek($field, true, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, true, 6);

		}

		public function testMatches_singleValue() {

			$field = new DayOfWeekField('5', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, false, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_singleValue_named() {

			$field = new DayOfWeekField('FRI', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, false, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);


		}

		public function testMatches_list() {

			$field = new DayOfWeekField('3,5', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, false, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_list_named() {

			$field = new DayOfWeekField('WED,FRI', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, false, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_range() {

			$field = new DayOfWeekField('3-5', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_range_named() {

			$field = new DayOfWeekField('WED-FRI', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_increment() {

			$field = new DayOfWeekField('*/2', $this->timezone);

			$this->matchDayOfWeek($field, true, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, false, 5);
			$this->matchDayOfWeek($field, true, 6);

		}

		public function testMatches_increment_offset() {

			$field = new DayOfWeekField('3/2', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, false, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_increment_offset_named() {

			$field = new DayOfWeekField('WED/2', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, false, 2);
			$this->matchDayOfWeek($field, true, 3);
			$this->matchDayOfWeek($field, false, 4);
			$this->matchDayOfWeek($field, true, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_increment_range() {

			$field = new DayOfWeekField('2-5/2', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, false, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_increment_range_named() {

			$field = new DayOfWeekField('TUE-FRI/2', $this->timezone);

			$this->matchDayOfWeek($field, false, 0);
			$this->matchDayOfWeek($field, false, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, false, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testMatches_complex() {

			$field = new DayOfWeekField('2-5/2,0,1-2', $this->timezone);

			$this->matchDayOfWeek($field, true, 0);
			$this->matchDayOfWeek($field, true, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, false, 5);
			$this->matchDayOfWeek($field, false, 6);


		}

		public function testMatches_complex_named() {

			$field = new DayOfWeekField('TUE-FRI/2,0,1-2', $this->timezone);

			$this->matchDayOfWeek($field, true, 0);
			$this->matchDayOfWeek($field, true, 1);
			$this->matchDayOfWeek($field, true, 2);
			$this->matchDayOfWeek($field, false, 3);
			$this->matchDayOfWeek($field, true, 4);
			$this->matchDayOfWeek($field, false, 5);
			$this->matchDayOfWeek($field, false, 6);

		}

		public function testNextAfter_wildcard() {

			$field = new DayOfWeekField('*', $this->timezone);

			$this->assertNextTs('2019-03-19 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-23 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-25 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-26 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-30 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);

			$this->assertNextOverflows('2019-03-31 00:00:00', $field);


		}

		public function testNextAfter_0ForSunday() {

			$field = new DayOfWeekField('0', $this->timezone);

			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);

			$this->assertNextOverflows('2019-03-31 00:00:00', $field);


		}

		public function testNextAfter_7ForSunday() {

			$field = new DayOfWeekField('0', $this->timezone);

			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);

			$this->assertNextOverflows('2019-03-31 00:00:00', $field);


		}

		public function testNextAfter_allDayNames() {

			$field = new DayOfWeekField('MON,TUE,WED,THU,FRI,SAT,SUN', $this->timezone);

			$this->assertNextTs('2019-03-19 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-23 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-25 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-26 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-30 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);

			$this->assertNextOverflows('2019-03-31 00:00:00', $field);


		}

		public function testNextAfter_singleValue() {

			$field = new DayOfWeekField('5', $this->timezone);

			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);

		}

		public function testNextAfter_singleValue_named() {

			$field = new DayOfWeekField('FRI', $this->timezone);

			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);

		}

		public function testNextAfter_list() {

			$field = new DayOfWeekField('3,5', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);

		}

		public function testNextAfter_listNamed() {

			$field = new DayOfWeekField('WED,FRI', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);

		}

		public function testNextAfter_range() {

			$field = new DayOfWeekField('3-5', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);

		}

		public function testNextAfter_named() {

			$field = new DayOfWeekField('WED-FRI', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);

		}

		public function testNextAfter_increment() {

			$field = new DayOfWeekField('*/2', $this->timezone);

			$this->assertNextTs('2019-03-19 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-23 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-23 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-26 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-26 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-30 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextTs('2019-03-30 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);
		}

		public function testNextAfter_increment_offset_named() {

			$field = new DayOfWeekField('WED/2', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);
		}

		public function testNextAfter_increment_range() {

			$field = new DayOfWeekField('3-6/2', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);
		}

		public function testNextAfter_increment_range_named() {

			$field = new DayOfWeekField('WED-FRI/2', $this->timezone);

			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-20 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-22 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-27 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-29 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextOverflows('2019-03-29 00:00:00', $field);
			$this->assertNextOverflows('2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);
		}

		public function testNextAfter_complex() {

			$field = new DayOfWeekField('2-5/2,0,1-2', $this->timezone);

			$this->assertNextTs('2019-03-19 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-25 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-26 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);
		}

		public function testNextAfter_complex_named() {

			$field = new DayOfWeekField('TUE-FRI/2,0,1-2', $this->timezone);

			$this->assertNextTs('2019-03-19 00:00:00', '2019-03-18 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-19 00:00:00', $field);
			$this->assertNextTs('2019-03-21 00:00:00', '2019-03-20 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-21 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-22 00:00:00', $field);
			$this->assertNextTs('2019-03-24 00:00:00', '2019-03-23 00:00:00', $field);
			$this->assertNextTs('2019-03-25 00:00:00', '2019-03-24 00:00:00', $field);

			$this->assertNextTs('2019-03-26 00:00:00', '2019-03-25 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-26 00:00:00', $field);
			$this->assertNextTs('2019-03-28 00:00:00', '2019-03-27 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-28 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-29 00:00:00', $field);
			$this->assertNextTs('2019-03-31 00:00:00', '2019-03-30 00:00:00', $field);
			$this->assertNextOverflows('2019-03-31 00:00:00', $field);
		}
	}