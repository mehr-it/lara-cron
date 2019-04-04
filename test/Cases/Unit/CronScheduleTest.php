<?php


	namespace MehrItLaraCronTest\Cases\Unit;


	use MehrIt\LaraCron\Contracts\CronExpression;
	use MehrIt\LaraCron\Contracts\CronJob;
	use MehrIt\LaraCron\CronSchedule;
	use PHPUnit\Framework\MockObject\MockObject;

	class CronScheduleTest extends TestCase
	{

		public function testGettersSettersConstructor() {

			/** @var CronExpression|MockObject $exp1 */
			$exp1 = $this->getMockBuilder(CronExpression::class)->getMock();

			/** @var CronExpression|MockObject $exp2 */
			$exp2 = $this->getMockBuilder(CronExpression::class)->getMock();

			$job1 = $this->getMockBuilder(CronJob::class)->getMock();

			$job2 = $this->getMockBuilder(CronJob::class)->getMock();


			// constructor
			$schedule = new CronSchedule($exp1, $job1, 'key1', 'group1', true, 5);

			$this->assertSame($exp1, $schedule->getExpression());
			$this->assertSame($job1, $schedule->getJob());
			$this->assertSame('key1', $schedule->getKey());
			$this->assertSame('group1', $schedule->getGroup());
			$this->assertSame(true, $schedule->isActive());
			$this->assertSame(5, $schedule->getCatchupTimeout());


			// setters
			$this->assertSame($schedule, $schedule->setExpression($exp2));
			$this->assertSame($schedule, $schedule->setJob($job2));
			$this->assertSame($schedule, $schedule->setKey('key2'));
			$this->assertSame($schedule, $schedule->setGroup('group2'));
			$this->assertSame($schedule, $schedule->setActive(false));
			$this->assertSame($schedule, $schedule->setCatchupTimeout(10));

			$this->assertSame($exp2, $schedule->getExpression());
			$this->assertSame($job2, $schedule->getJob());
			$this->assertSame('key2', $schedule->getKey());
			$this->assertSame('group2', $schedule->getGroup());
			$this->assertSame(false, $schedule->isActive());
			$this->assertSame(10, $schedule->getCatchupTimeout());
		}

	}