<?php


	namespace MehrItLaraCronTest\Cases\Unit\Cron;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Cron\CronExpression;
	use MehrItLaraCronTest\Cases\Unit\CronTestCase;

	class CronExpressionTest extends CronTestCase
	{

		protected function checkCron($expression, $from, $to, $inc, $expected) {
			$expr = new CronExpression($expression, $this->timezone);

			$curr = (new Carbon($from, $this->timezone));
			$end  = (new Carbon($to, $this->timezone));

			$endTs = $end->getTimestamp();

			if ($expected instanceof \Closure) {
				$expected = $expected($curr->copy(), $end->copy());
			}
			$expected = array_map(function($value) {
				if (is_string($value))
					return (new \DateTime($value, $this->timezone))->getTimestamp();
				else
					return $value;
			}, $expected);


			$currTs = $curr->getTimestamp();
			$nextMatching = array_shift($expected);
			$i = 1;
			while ($currTs < $endTs) {

				$curr = (new \DateTime('@' . $currTs))->setTimezone($this->timezone);
				$currOut     = $curr->format('Y-m-d H:i:s');
				$shouldEqual = ($nextMatching == $currTs);
				$this->assertEquals($shouldEqual, $expr->matches($currTs), "[$i] Testing matches($currTs) for $currOut ");


				if ($shouldEqual) {
					$nextMatching = array_shift($expected);
					++$i;
				}

				$this->assertEquals($nextMatching, $expr->nextAfter($currTs, $nextMatching + 1), "[$i] Testing nextAfter($currTs) for $currOut");

				if (is_numeric($inc))
					$currTs += $inc;
				else
					$currTs = (clone $curr)->modify($inc)->getTimestamp();
			}

			$this->assertEmpty($expected);
		}

		public function testGetExpression() {

			$expr = '13 9 12 * *';
			$timezone = new \DateTimeZone('UTC');

			$cronExpr = new CronExpression($expr, $timezone);

			$this->assertSame($expr, $cronExpr->getExpression());
		}

		public function testGetExpression_timezoneString() {

			$expr = '13 9 12 * *';

			$cronExpr = new CronExpression($expr, 'UTC');

			$this->assertSame($expr, $cronExpr->getExpression());
		}

		public function testGetTimezone() {

			$expr     = '13 9 12 * *';
			$timezone = new \DateTimeZone('UTC');

			$cronExpr = new CronExpression($expr, $timezone);

			$this->assertSame($timezone, $cronExpr->getTimezone());
		}

		public function test_everyMinute_noonToNoon() {


			$this->checkCron('* * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					$ret[] = $expTs;

					$exp->addMinute();
				};

				return $ret;
			});



		}

		public function test_everyEvenMinute_noonToNoon() {


			$this->checkCron('*/2 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->minute % 2 === 0)
						$ret[] = $expTs;

					$exp->addMinute();
				};

				return $ret;
			});

		}

		public function test_everyUnevenMinute_noonToNoon() {


			$this->checkCron('1/2 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->minute % 2 === 1)
						$ret[] = $expTs;

					$exp->addMinute();
				};

				return $ret;
			});

		}

		public function test_every3Minutes_noonToNoon() {


			$this->checkCron('*/3 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->minute % 3 === 0)
						$ret[] = $expTs;

					$exp->addMinute();
				};

				return $ret;
			});

		}

		public function test_every5Minutes_noonToNoon() {


			$this->checkCron('*/5 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function(Carbon $from, Carbon $to) {
				$ret = [];
				$exp    = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					$ret[] = $expTs;

					$exp->addMinutes(5);
				};

				return $ret;
			});

		}

		public function test_every5MinutesInFirstHalfHour_noonToNoon() {


			$this->checkCron('0-30/5 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function(Carbon $from, Carbon $to) {
				$ret = [];
				$exp    = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->minute <= 30)
						$ret[] = $expTs;

					$exp->addMinutes(5);
				};

				return $ret;
			});

		}

		public function test_every5Minutes_endOfMonth_31() {

			$this->checkCron('*/5 * * * *', '2019-01-31 23:50:00', '2019-02-01 00:05:00', 60, [
				'2019-01-31 23:50:00',
				'2019-01-31 23:55:00',
				'2019-02-01 00:00:00',
				'2019-02-01 00:05:00',
			]);

		}

		public function test_every5Minutes_endOfMonth_30() {

			$this->checkCron('*/5 * * * *', '2019-04-30 23:50:00', '2019-05-01 00:05:00', 60, [
				'2019-04-30 23:50:00',
				'2019-04-30 23:55:00',
				'2019-05-01 00:00:00',
				'2019-05-01 00:05:00',
			]);

		}

		public function test_every5Minutes_endOfMonth_29() {

			$this->checkCron('*/5 * * * *', '2020-02-29 23:50:00', '2020-03-01 00:05:00', 60, [
				'2020-02-29 23:50:00',
				'2020-02-29 23:55:00',
				'2020-03-01 00:00:00',
				'2020-03-01 00:05:00',
			]);

		}

		public function test_every5Minutes_endOfMonth_28() {

			$this->checkCron('*/5 * * * *', '2019-02-28 23:50:00', '2019-03-01 00:05:00', 60, [
				'2019-02-28 23:50:00',
				'2019-02-28 23:55:00',
				'2019-03-01 00:00:00',
				'2019-03-01 00:05:00',
			]);

		}

		public function test_every5Minutes_endOfYear() {

			$this->checkCron('*/5 * * * *', '2019-12-31 23:50:00', '2020-01-01 00:05:00', 60, [
				'2019-12-31 23:50:00',
				'2019-12-31 23:55:00',
				'2020-01-01 00:00:00',
				'2020-01-01 00:05:00',
			]);

		}

		public function test_every5Minutes_dstStart() {

			$this->checkCron('*/5 * * * *', '2019-03-31 01:50:00', '2019-03-31 03:05:00', 60, [
				'2019-03-31 01:50:00',
				'2019-03-31 01:55:00',
				'2019-03-31 03:00:00',
				'2019-03-31 03:05:00',
			]);

		}

		public function test_every5Minutes_dstEnd() {

			$this->checkCron('*/5 * * * *', '2019-10-27 01:50:00', '2019-10-27 04:05:00', 60, [
				'2019-10-27 01:50:00',
				'2019-10-27 01:55:00',
				(new \DateTime('2019-10-27 01:00:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:05:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:10:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:20:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:25:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:30:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:35:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:40:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:45:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:50:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:55:00', $this->timezone))->getTimestamp() + 3600,
				(new \DateTime('2019-10-27 01:00:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:05:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:10:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:20:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:25:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:30:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:35:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:40:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:45:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:50:00', $this->timezone))->getTimestamp() + 7200,
				(new \DateTime('2019-10-27 01:55:00', $this->timezone))->getTimestamp() + 7200,
				'2019-10-27 03:00:00',
				'2019-10-27 03:05:00',
				'2019-10-27 03:10:00',
				'2019-10-27 03:15:00',
				'2019-10-27 03:20:00',
				'2019-10-27 03:25:00',
				'2019-10-27 03:30:00',
				'2019-10-27 03:35:00',
				'2019-10-27 03:40:00',
				'2019-10-27 03:45:00',
				'2019-10-27 03:50:00',
				'2019-10-27 03:55:00',
				'2019-10-27 04:00:00',
				'2019-10-27 04:05:00',
			]);

		}

		public function test_everyHourAt15_45_noonToNoon() {


			$this->checkCron('15,45 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					switch($exp->minute) {
						case 15:
						case 45:
							$ret[] = $expTs;
					}

					$exp->addMinutes(5);
				};

				return $ret;
			});

		}

		public function test_everyHourBetween8and15At15_45_noonToNoon() {


			$this->checkCron('15,45 8-15 * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					switch($exp->minute) {
						case 15:
						case 45:
							if ($exp->hour >= 8 && $exp->hour <= 15)
								$ret[] = $expTs;
					}

					$exp->addMinutes(5);
				};

				return $ret;
			});

		}

		public function test_everyHourAt15_30_dstStart() {


			$this->checkCron('15,30 * * * *', '2019-03-31 00:15:00', '2019-03-31 05:15:00', 60, [
				'2019-03-31 00:15:00',
				'2019-03-31 00:30:00',
				'2019-03-31 01:15:00',
				'2019-03-31 01:30:00',
				'2019-03-31 03:15:00',
				'2019-03-31 03:30:00',
				'2019-03-31 04:15:00',
				'2019-03-31 04:30:00',
				'2019-03-31 05:15:00',
			]);

		}

		public function test_everyHourAt15_30_dstEnd() {


			$this->checkCron('15,30 * * * *', '2019-10-27 00:15:00', '2019-10-27 06:15:00', 60, [
				'2019-10-27 00:15:00',
				'2019-10-27 00:30:00',
				'2019-10-27 01:15:00',
				'2019-10-27 01:30:00',
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 3600, // 02:00
				(new \DateTime('2019-10-27 01:30:00', $this->timezone))->getTimestamp() + 3600, // 02:00
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 3600 * 2, // 03:00 => 02:00
				(new \DateTime('2019-10-27 01:30:00', $this->timezone))->getTimestamp() + 3600 * 2, // 03:00 => 02:00
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 3600 * 3, // 03:00
				(new \DateTime('2019-10-27 01:30:00', $this->timezone))->getTimestamp() + 3600 * 3, // 03:00
				'2019-10-27 04:15:00',
				'2019-10-27 04:30:00',
				'2019-10-27 05:15:00',
				'2019-10-27 05:30:00',
				'2019-10-27 06:15:00',
			]);

		}

		public function test_everyHour_noonToNoon() {


			$this->checkCron('0 * * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					$ret[] = $expTs;

					$exp->addHour();
				};

				return $ret;
			});

		}

		public function test_everyEvenHour_noonToNoon() {


			$this->checkCron('0 */2 * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour % 2 === 0)
						$ret[] = $expTs;

					$exp->addHour();
				};

				return $ret;
			});

		}

		public function test_everyUnevenHour_noonToNoon() {


			$this->checkCron('0 1/2 * * *', '2019-01-01 12:00:00', '2019-01-02 11:59:00', 60, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour % 2 === 1)
						$ret[] = $expTs;

					$exp->addHour();
				};

				return $ret;
			});

		}

		public function test_everyHour_endOfMonth_31() {

			$this->checkCron('0 * * * *', '2019-01-31 22:00:00', '2019-02-01 02:00:00', 60, [
				'2019-01-31 22:00:00',
				'2019-01-31 23:00:00',
				'2019-02-01 00:00:00',
				'2019-02-01 01:00:00',
				'2019-02-01 02:00:00',
			]);

		}

		public function test_everyHour_endOfMonth_30() {

			$this->checkCron('0 * * * *', '2019-04-30 22:00:00', '2019-05-01 02:00:00', 60, [
				'2019-04-30 22:00:00',
				'2019-04-30 23:00:00',
				'2019-05-01 00:00:00',
				'2019-05-01 01:00:00',
				'2019-05-01 02:00:00',
			]);

		}

		public function test_everyHour_endOfMonth_29() {

			$this->checkCron('0 * * * *', '2020-02-29 22:00:00', '2020-03-01 02:00:00', 60, [
				'2020-02-29 22:00:00',
				'2020-02-29 23:00:00',
				'2020-03-01 00:00:00',
				'2020-03-01 01:00:00',
				'2020-03-01 02:00:00',
			]);

		}

		public function test_everyHour_endOfMonth_28() {

			$this->checkCron('0 * * * *', '2019-02-28 22:00:00', '2019-03-01 02:00:00', 60, [
				'2019-02-28 22:00:00',
				'2019-02-28 23:00:00',
				'2019-03-01 00:00:00',
				'2019-03-01 01:00:00',
				'2019-03-01 02:00:00',
			]);

		}

		public function test_everyHour_endOfYear() {

			$this->checkCron('0 * * * *', '2019-12-31 22:00:00', '2020-01-01 02:00:00', 60, [
				'2019-12-31 22:00:00',
				'2019-12-31 23:00:00',
				'2020-01-01 00:00:00',
				'2020-01-01 01:00:00',
				'2020-01-01 02:00:00',
			]);

		}

		public function test_everyHour_dstStart() {

			$this->checkCron('0 * * * *', '2019-03-31 00:00:00', '2019-03-31 05:00:00', 60, [
				'2019-03-31 00:00:00',
				'2019-03-31 01:00:00',
				'2019-03-31 03:00:00',
				'2019-03-31 04:00:00',
				'2019-03-31 05:00:00',
			]);

		}

		public function test_everyHour_dstEnd() {

			$this->checkCron('0 * * * *', '2019-10-27 00:00:00', '2019-10-27 06:00:00', 60, [
				'2019-10-27 00:00:00',
				'2019-10-27 01:00:00',
				(new \DateTime('2019-10-27 01:00:00', $this->timezone))->getTimestamp() + 3600, // 02:00
				(new \DateTime('2019-10-27 01:00:00', $this->timezone))->getTimestamp() + 3600 * 2, // 03:00 => 02:00
				(new \DateTime('2019-10-27 01:00:00', $this->timezone))->getTimestamp() + 3600 * 3, // 03:00
				'2019-10-27 04:00:00',
				'2019-10-27 05:00:00',
				'2019-10-27 06:00:00',
			]);

		}

		public function test_everyDay_at1215_allMonth() {

			$this->checkCron('15 12 * * *', '2019-01-01 00:15:00', '2019-01-31 23:59:59', 900, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15)
						$ret[] = $expTs;

					$exp->addMinutes(15);
				};

				return $ret;
			});

		}

		public function test_everyDay_at1215_endOfMonth_31() {

			$this->checkCron('15 12 * * *', '2019-01-25 00:15:00', '2019-02-05 23:59:59', 900, [
				'2019-01-25 12:15:00',
				'2019-01-26 12:15:00',
				'2019-01-27 12:15:00',
				'2019-01-28 12:15:00',
				'2019-01-29 12:15:00',
				'2019-01-30 12:15:00',
				'2019-01-31 12:15:00',
				'2019-02-01 12:15:00',
				'2019-02-02 12:15:00',
				'2019-02-03 12:15:00',
				'2019-02-04 12:15:00',
				'2019-02-05 12:15:00',

			]);

		}

		public function test_everyDay_at1215_endOfMonth_30() {

			$this->checkCron('15 12 * * *', '2019-04-25 00:15:00', '2019-05-05 23:59:59', 900, [
				'2019-04-25 12:15:00',
				'2019-04-26 12:15:00',
				'2019-04-27 12:15:00',
				'2019-04-28 12:15:00',
				'2019-04-29 12:15:00',
				'2019-04-30 12:15:00',
				'2019-05-01 12:15:00',
				'2019-05-02 12:15:00',
				'2019-05-03 12:15:00',
				'2019-05-04 12:15:00',
				'2019-05-05 12:15:00',

			]);

		}

		public function test_everyDay_at1215_endOfMonth_29() {

			$this->checkCron('15 12 * * *', '2020-02-25 00:15:00', '2020-03-05 23:59:59', 900, [
				'2020-02-25 12:15:00',
				'2020-02-26 12:15:00',
				'2020-02-27 12:15:00',
				'2020-02-28 12:15:00',
				'2020-02-29 12:15:00',
				'2020-03-01 12:15:00',
				'2020-03-02 12:15:00',
				'2020-03-03 12:15:00',
				'2020-03-04 12:15:00',
				'2020-03-05 12:15:00',

			]);

		}

		public function test_everyDay_at1215_endOfMonth_28() {

			$this->checkCron('15 12 * * *', '2019-02-25 00:15:00', '2019-03-05 23:59:59', 900, [
				'2019-02-25 12:15:00',
				'2019-02-26 12:15:00',
				'2019-02-27 12:15:00',
				'2019-02-28 12:15:00',
				'2019-03-01 12:15:00',
				'2019-03-02 12:15:00',
				'2019-03-03 12:15:00',
				'2019-03-04 12:15:00',
				'2019-03-05 12:15:00',

			]);

		}

		public function test_everyDay_at0215_dstStart() {

			$this->checkCron('15 2 * * *', '2019-03-30 02:15:00', '2019-04-01 05:00:00', 60, [
				'2019-03-30 02:15:00',
				'2019-03-31 03:15:00',
				'2019-04-01 02:15:00',
			]);

		}

		public function test_everyDay_at0215_dstEnd() {

			$this->checkCron('15 2 * * *', '2019-10-26 00:00:00', '2019-10-28 06:00:00', 60, [
				'2019-10-26 02:15:00',
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 3600, // 02:15 (not repeated)
				'2019-10-28 02:15:00',
			]);

		}

		public function test_everyDay_at0315_dstStart() {

			$this->checkCron('15 3 * * *', '2019-03-30 02:15:00', '2019-04-01 05:00:00', 60, [
				'2019-03-30 03:15:00',
				'2019-03-31 03:15:00',
				'2019-04-01 03:15:00',
			]);

		}

		public function test_everyDay_at0315_dstEnd() {

			$this->checkCron('15 3 * * *', '2019-10-26 00:00:00', '2019-10-28 06:00:00', 60, [
				'2019-10-26 03:15:00',
				(new \DateTime('2019-10-27 01:15:00', $this->timezone))->getTimestamp() + 3600 * 3, // 03:15 (not repeated)
				'2019-10-28 03:15:00',
			]);

		}

		public function test_everySunday_at1215_allMonth() {

			$this->checkCron('15 12 * * SUN', '2019-01-01 12:15:00', '2019-01-31 23:59:59', 3600, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->dayOfWeek == 0)
						$ret[] = $expTs;

					$exp->addDay();
				};

				return $ret;
			});

		}

		public function test_everySunday_at1215_endOfMonth_31() {

			$this->checkCron('15 12 * * SUN', '2019-01-25 12:15:00', '2019-02-05 23:59:59', 3600, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->dayOfWeek == 0)
						$ret[] = $expTs;

					$exp->addDay();
				};

				return $ret;
			});

		}

		public function test_everySunday_at1215_endOfMonth_30() {

			$this->checkCron('15 12 * * SUN', '2019-04-25 12:15:00', '2019-05-05 23:59:59', 3600, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->dayOfWeek == 0)
						$ret[] = $expTs;

					$exp->addDay();
				};

				return $ret;
			});

		}

		public function test_everySunday_at1215_endOfMonth_29() {

			$this->checkCron('15 12 * * SUN', '2020-02-25 12:15:00', '2020-03-05 23:59:59', 3600, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->dayOfWeek == 0)
						$ret[] = $expTs;

					$exp->addDay();
				};

				return $ret;
			});

		}

		public function test_everySunday_at1215_endOfMonth_28() {

			$this->checkCron('15 12 * * SUN', '2019-02-25 12:15:00', '2019-03-05 23:59:59', 3600, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->dayOfWeek == 0)
						$ret[] = $expTs;

					$exp->addDay();
				};

				return $ret;
			});

		}

		public function test_weekdays_at1215_allMonth() {

			$this->checkCron('15 12 * * MON-FRI', '2019-01-01 12:15:00', '2019-01-31 23:59:59', 3600 * 4, function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->dayOfWeek >= 1 && $exp->dayOfWeek <= 5)
						$ret[] = $expTs;

					$exp->addDay()->setTime(12, 15, 00);
				};

				return $ret;
			});

		}

		public function test_15thOfMonth_at1215_4_years() {

			$this->checkCron('15 12 15 * *', '2019-01-01 12:15:00', '2023-12-15 12:15:00', '+1 day', function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->day == 15)
						$ret[] = $expTs;

					$exp->addDay()->setTime(12, 15, 00);
				};

				return $ret;
			});

		}

		public function test_15thOfMonthOrSunday_at1215_4_years() {

			$this->checkCron('15 12 15 * SUN', '2019-01-01 12:15:00', '2023-12-15 12:15:00', '+1 day', function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && ($exp->day == 15 || $exp->dayOfWeek == 0))
						$ret[] = $expTs;

					$exp->addDay()->setTime(12, 15, 00);
				};

				return $ret;
			});

		}

		public function test_everySecondOfMonth1215FromFebToJun_4_years() {

			$this->checkCron('15 12 2 FEB-JUN *', '2019-01-01 12:15:00', '2023-12-15 12:15:00', '+1 day', function (Carbon $from, Carbon $to) {
				$ret = [];
				$exp = $from->copy();
				while (($expTs = $exp->getTimestamp()) <= $to->getTimestamp()) {
					if ($exp->hour == 12 && $exp->minute == 15 && $exp->day == 2 && $exp->month >= 2 && $exp->month <= 6)
						$ret[] = $expTs;

					$exp->addDay()->setTime(12, 15, 00);
				};

				return $ret;
			});

		}

	}