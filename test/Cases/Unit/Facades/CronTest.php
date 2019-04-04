<?php


	namespace MehrItLaraCronTest\Cases\Unit\Facades;


	use MehrIt\LaraCron\Contracts\CronManager;
	use MehrIt\LaraCron\Facades\Cron;
	use MehrItLaraCronTest\Cases\Unit\TestCase;

	class CronTest extends TestCase
	{
		public function testAncestorCall() {
			// mock ancestor
			$mock = $this->mockAppSingleton(CronManager::class, CronManager::class);
			$mock->expects($this->once())
				->method('delete')
				->with('key');

			Cron::delete('key');
		}
	}