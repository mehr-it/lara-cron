<?php


	namespace MehrItLaraCronTest\Cases\Unit\Cron\Field;


	use Carbon\Carbon;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;
	use MehrItLaraCronTest\Cases\Unit\CronTestCase;

	abstract class FieldTest extends CronTestCase
	{
		protected function assertNextTs(string $expected, string $current, CronField $field) {

			$ts = (new Carbon($current, $this->timezone))->timestamp;

			try {
				$next = $field->nextAfter($ts);
			}
			catch (OutOfRangeException $ex) {
				$this->fail('Not expected to be out of range for ' . $current);
			}


			$this->assertEquals($expected, Carbon::createFromTimestamp($next)->format('Y-m-d H:i:s'));
		}

		protected function assertNextOverflows(string $current, CronField $field) {
			$ts = (new Carbon($current, $this->timezone))->timestamp;

			try {
				$field->nextAfter($ts);
				$this->fail('Expected ' . OutOfRangeException::class . ' to be thrown for ' . $current);
			}
			catch (OutOfRangeException $ex) {
				$this->assertFalse(false);
			}
		}
	}