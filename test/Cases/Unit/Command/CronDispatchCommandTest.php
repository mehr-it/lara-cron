<?php


	namespace MehrItLaraCronTest\Cases\Unit\Command;


	use MehrIt\LaraCron\Contracts\CronManager;
	use MehrItLaraCronTest\Cases\Unit\TestCase;

	class CronDispatchCommandTest extends TestCase
	{

		public function testDispatched() {

			$this->mockAppSingleton(CronManager::class, CronManager::class)
				->expects($this->once())
				->method('dispatch')
				->with(60)
				->willReturn(4);


			$this->artisan('cron:dispatch', ['period' => 60])
				->expectsOutput('Dispatched 4 cron job(s).')
				->assertExitCode(0);
			;

		}

		public function testDispatched_invalidPeriod() {

			$this->mockAppSingleton(CronManager::class, CronManager::class)
				->expects($this->never())
				->method('dispatch');


			$this->artisan('cron:dispatch', ['period' => 0])
				->assertExitCode(1);
			;

		}

	}