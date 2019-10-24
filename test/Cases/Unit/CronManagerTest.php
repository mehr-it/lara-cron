<?php


	namespace MehrItLaraCronTest\Cases\Unit;


	use Illuminate\Bus\Queueable;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Support\Facades\Queue;
	use MehrIt\LaraCron\Contracts\CronExpression;
	use MehrIt\LaraCron\Contracts\CronJob;
	use MehrIt\LaraCron\Contracts\CronSchedule as CronScheduleContract;
	use MehrIt\LaraCron\Contracts\CronScheduleLog;
	use MehrIt\LaraCron\Contracts\CronStore;
	use MehrIt\LaraCron\CronManager;
	use MehrIt\LaraCron\CronSchedule;
	use MehrIt\LaraCron\Log\CronScheduleLogEntry;
	use MehrIt\LaraCron\Log\EloquentScheduleLog;
	use MehrIt\LaraCron\Log\MemoryScheduleLog;
	use MehrIt\LaraCron\Queue\InteractsWithCron;
	use MehrIt\LaraCron\Store\CronTabEntry;
	use MehrIt\LaraCron\Store\EloquentStore;
	use MehrIt\LaraCron\Store\MemoryStore;
	use PHPUnit\Framework\MockObject\MockObject;

	class CronManagerTest extends TestCase
	{

		public function testGetStore_memory_stringConfig() {

			config()->set('cron.store', 'memory');

			$manager = new CronManager();

			$this->assertInstanceOf(MemoryStore::class, $manager->getStore());

		}

		public function testGetStore_memory_arrayConfig() {

			config()->set('cron.store', [
				'driver' => 'memory'
			]);

			$manager = new CronManager();

			$this->assertInstanceOf(MemoryStore::class, $manager->getStore());

		}

		public function testGetStore_eloquent_stringConfig() {

			config()->set('cron.store', 'eloquent');

			$manager = new CronManager();

			$this->assertInstanceOf(EloquentStore::class, $manager->getStore());
			$this->assertSame(CronTabEntry::class, $manager->getStore()->getModel());

		}

		public function testGetStore_eloquent_arrayConfig() {

			config()->set('cron.store', [
				'driver' => 'eloquent'
			]);

			$manager = new CronManager();

			$this->assertInstanceOf(EloquentStore::class, $manager->getStore());
			$this->assertSame(CronTabEntry::class, $manager->getStore()->getModel());

		}

		public function testGetStore_eloquent_arrayConfig_withModel() {

			config()->set('cron.store', [
				'driver' => 'eloquent',
				'model' => CronManagerTestStoreModel::class
			]);

			$manager = new CronManager();

			$this->assertInstanceOf(EloquentStore::class, $manager->getStore());
			$this->assertSame(CronManagerTestStoreModel::class, $manager->getStore()->getModel());

		}

		public function testGetStore_customCreator() {

			$config = [
				'driver'       => 'custom',
				'customOption' => true
			];

			config()->set('cron.store', $config);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();

			$manager = new CronManager();

			$manager->registerStoreDriver('custom', function($passedConfig) use ($config, $storeMock) {

				$this->assertSame($config, $passedConfig);

				return $storeMock;
			});


			$this->assertSame($storeMock, $manager->getStore());
		}

		public function testGetStore_customCreator_overridesDefault() {

			$config = [
				'driver'       => 'eloquent',
				'customOption' => true
			];

			config()->set('cron.store', $config);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();

			$manager = new CronManager();

			$manager->registerStoreDriver('eloquent', function($passedConfig) use ($config, $storeMock) {

				$this->assertSame($config, $passedConfig);

				return $storeMock;
			});


			$this->assertSame($storeMock, $manager->getStore());
		}


		public function testGetScheduleLog_memory_stringConfig() {

			config()->set('cron.scheduleLog', 'memory');

			$manager = new CronManager();

			$this->assertInstanceOf(MemoryScheduleLog::class, $manager->getScheduleLog());

		}

		public function testGetScheduleLog_memory_arrayConfig() {

			config()->set('cron.scheduleLog', [
				'driver' => 'memory'
			]);

			$manager = new CronManager();

			$this->assertInstanceOf(MemoryScheduleLog::class, $manager->getScheduleLog());

		}

		public function testGetScheduleLog_eloquent_stringConfig() {

			config()->set('cron.scheduleLog', 'eloquent');

			$manager = new CronManager();

			$this->assertInstanceOf(EloquentScheduleLog::class, $manager->getScheduleLog());
			$this->assertSame(CronScheduleLogEntry::class, $manager->getScheduleLog()->getModel());

		}

		public function testGetScheduleLog_eloquent_arrayConfig() {

			config()->set('cron.scheduleLog', [
				'driver' => 'eloquent'
			]);

			$manager = new CronManager();

			$this->assertInstanceOf(EloquentScheduleLog::class, $manager->getScheduleLog());
			$this->assertSame(CronScheduleLogEntry::class, $manager->getScheduleLog()->getModel());

		}

		public function testGetScheduleLog_eloquent_arrayConfig_withModel() {

			config()->set('cron.scheduleLog', [
				'driver' => 'eloquent',
				'model'  => CronManagerTestScheduleLogModel::class,
			]);

			$manager = new CronManager();

			$this->assertInstanceOf(EloquentScheduleLog::class, $manager->getScheduleLog());
			$this->assertSame(CronManagerTestScheduleLogModel::class, $manager->getScheduleLog()->getModel());

		}

		public function testGetScheduleLog_customCreator() {
			$config = [
				'driver'       => 'custom',
				'customOption' => true
			];

			config()->set('cron.scheduleLog', $config);

			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();

			$manager = new CronManager();

			$manager->registerScheduleLogDriver('custom', function ($passedConfig) use ($config, $logMock) {

				$this->assertSame($config, $passedConfig);

				return $logMock;
			});


			$this->assertSame($logMock, $manager->getScheduleLog());
		}

		public function testGetScheduleLog_customCreator_overridesDefault() {
			$config = [
				'driver'       => 'eloquent',
				'customOption' => true
			];

			config()->set('cron.scheduleLog', $config);

			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();

			$manager = new CronManager();

			$manager->registerScheduleLogDriver('eloquent', function ($passedConfig) use ($config, $logMock) {

				$this->assertSame($config, $passedConfig);

				return $logMock;
			});


			$this->assertSame($logMock, $manager->getScheduleLog());
		}

		public function testSchedule_withoutOptionalParameters() {

			config()->set('cron.store', 'testing');

			$createdSchedule = null;

			/** @var CronExpression|MockObject $cronExpression */
			$cronExpression = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();

			$job = $this->getMockBuilder(\MehrIt\LaraCron\Contracts\CronJob::class)->getMock();

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('put')
				->with($this->callback(function($schedule) use (&$createdSchedule, $cronExpression, $job) {

					// remember the passed schedule
					$createdSchedule = $schedule;

					return
						$schedule instanceof CronSchedule &&
						$schedule->getExpression() === $cronExpression &&
						$schedule->getJob() === $job &&
						$schedule->getKey() &&
						$schedule->getGroup() === null &&
						$schedule->isActive() === true &&
						$schedule->getCatchupTimeout() === 0;

				}))
				->willReturnSelf();

			$manager  = new CronManager();
			$manager->registerStoreDriver('testing', function() use ($storeMock) {
				return $storeMock;
			});


			$ret = $manager->schedule($cronExpression, $job);

			$this->assertSame($createdSchedule, $ret);
		}

		public function testSchedule() {

			config()->set('cron.store', 'testing');

			$createdSchedule = null;

			/** @var CronExpression|MockObject $cronExpression */
			$cronExpression = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();

			$job = $this->getMockBuilder(\MehrIt\LaraCron\Contracts\CronJob::class)->getMock();

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('put')
				->with($this->callback(function($schedule) use (&$createdSchedule, $cronExpression, $job) {

					// remember the passed schedule
					$createdSchedule = $schedule;

					return
						$schedule instanceof CronSchedule &&
						$schedule->getExpression() === $cronExpression &&
						$schedule->getJob() === $job &&
						$schedule->getKey() === 'my-key' &&
						$schedule->getGroup() === 'my-group' &&
						$schedule->isActive() === false &&
						$schedule->getCatchupTimeout() === 10;

				}))
				->willReturnSelf();

			$manager  = new CronManager();
			$manager->registerStoreDriver('testing', function() use ($storeMock) {
				return $storeMock;
			});


			$ret = $manager->schedule($cronExpression, $job, 'my-key', 'my-group', false, 10);

			$this->assertSame($createdSchedule, $ret);
		}

		public function testDescribeAll() {

			config()->set('cron.store', 'testing');

			$schedule1Mock = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$schedule2Mock = $this->getMockBuilder(CronScheduleContract::class)->getMock();

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$schedule1Mock,
					$schedule2Mock,
				]));

			$manager = new CronManager();
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});

			$this->assertSame([$schedule1Mock, $schedule2Mock], iterator_to_array($manager->describeAll()));

		}

		public function testDescribeAll_withGroup() {

			config()->set('cron.store', 'testing');

			$schedule1Mock = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$schedule2Mock = $this->getMockBuilder(CronScheduleContract::class)->getMock();

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('all')
				->with('group-name')
				->willReturn(new \ArrayIterator([
					$schedule1Mock,
					$schedule2Mock,
				]));

			$manager = new CronManager();
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});

			$this->assertSame([$schedule1Mock, $schedule2Mock], iterator_to_array($manager->describeAll('group-name')));

		}

		public function testDescribe() {

			config()->set('cron.store', 'testing');

			$scheduleMock = $this->getMockBuilder(CronScheduleContract::class)->getMock();

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('get')
				->with('key1')
				->willReturn($scheduleMock);

			$manager = new CronManager();
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});

			$this->assertSame($scheduleMock, $manager->describe('key1'));

		}

		public function testDescribe_notExisting() {

			config()->set('cron.store', 'testing');

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('get')
				->with('key1')
				->willReturn(null);

			$manager = new CronManager();
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});

			$this->assertSame(null, $manager->describe('key1'));

		}

		public function testDelete() {

			config()->set('cron.store', 'testing');

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock->expects($this->once())
				->method('delete')
				->with('key1')
				->willReturnSelf();

			$manager = new CronManager();
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});

			$this->assertSame($manager, $manager->delete('key1'));

		}

		public function testLastScheduled() {

			config()->set('cron.scheduleLog', 'testing');

			$ts = time() - 500;

			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock->expects($this->once())
				->method('getLastSchedule')
				->with('key1')
				->willReturn($ts);

			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});

			$this->assertSame($ts, $manager->lastScheduled('key1'));

		}

		public function testLastScheduled_notYetScheduled() {

			config()->set('cron.scheduleLog', 'testing');

			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock->expects($this->once())
				->method('getLastSchedule')
				->with('key1')
				->willReturn(null);

			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});

			$this->assertSame(null, $manager->lastScheduled('key1'));

		}

		public function testDispatch_oneActive_notDispatchedYet() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(2))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$this->callback(function ($v) use ($now) {
							return $v == $now + 1000;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls(
					$now + 1000,
					null
				);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(1, $manager->dispatch(6000));

			// upcoming job
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($now) {
				return $job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now + 1000 &&
				       $job->getCronDispatchTs() >= $now - 10 && $job->getCronDispatchTs() <= $now &&
				       $job->delay <= 1000 && $job->delay >= 995;
			});
		}

		public function testDispatch_oneActive_notDispatchedYet_noInteractTrait() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$task = new CronManagerTestJob_withoutInteract();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(2))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$this->callback(function ($v) use ($now) {
							return $v == $now + 1000;
						})
					],
					[
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls(
					$now + 1000,
					null
				);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);



			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(1, $manager->dispatch(6000));

			// upcoming job
			Queue::assertPushed(CronManagerTestJob_withoutInteract::class, function ($job) use ($now) {
				return $job instanceof CronJob &&
				       $job->delay <= 1000 && $job->delay >= 995;
			});
		}

		public function testDispatch_oneActive_multipleTimesDue() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(4))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 1000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 2000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 3000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls(
					$now + 1000,
					$now + 2000,
					$now + 3000,
					null
				);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(3, $manager->dispatch(6000));

			// upcoming job
			$l = [
				'a' => 0,
				'b' => 0,
				'c' => 0
			];
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($now, &$l) {
				return ($job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now + 1000 &&
				       $job->getCronDispatchTs() >= $now - 10 && $job->getCronDispatchTs() <= $now &&
				       $job->delay <= 1000 && $job->delay >= 995 &&
						++$l['a']
				       ) ||
				       (
					       $job instanceof CronJob &&
					       $job->getCronScheduleKey() === 'key1' &&
					       $job->getCronScheduledTs() === $now + 2000 &&
					       $job->getCronDispatchTs() >= $now - 10 && $job->getCronDispatchTs() <= $now &&
					       $job->delay <= 2000 && $job->delay >= 1995 &&
					       ++$l['b']
				       )
				       ||
				       (
					       $job instanceof CronJob &&
					       $job->getCronScheduleKey() === 'key1' &&
					       $job->getCronScheduledTs() === $now + 3000 &&
					       $job->getCronDispatchTs() >= $now - 10 && $job->getCronDispatchTs() <= $now &&
					       $job->delay <= 3000 && $job->delay >= 2995
					       && ++$l['c']
				       )
					;
			});

			$this->assertEquals(['a' => 1, 'b' => 1, 'c' => 1], $l);

		}

		public function testDispatch_noneActive() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');


			$lastScheduled = null;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->never())
				->method('nextAfter');

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(0);
			$scheduleMock1
				->method('isActive')
				->willReturn(false);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);



			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(0, $manager->dispatch(6000));

			Queue::assertNothingPushed();
		}


		public function testDispatch_oneActive_yetNotDue() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->once())
				->method('nextAfter')
				->with(
					$this->callback(function ($v) use ($now) {
						return $now <= $v && $v <= $now + 10;
					}),
					$this->callback(function ($v) use ($now) {
						return $now + 500 <= $v && $v <= $now + 500 + 10;
					})
				)
				->willReturn(null);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(0);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(0, $manager->dispatch(500));

			Queue::assertNothingPushed();
		}

		public function testDispatch_oneActive_alreadyScheduled() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = $now + 100;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(2))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 500 <= $v && $v <= $now + 500 + 10;
						})
					],
					[
						$this->callback(function ($v) use ($now) {
							return $v == $now + 100;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 500 <= $v && $v <= $now + 500 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls(
					$now + 100,
					null
				);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(0);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(0, $manager->dispatch(500));

			Queue::assertNothingPushed();
		}


		public function testDispatch_oneActive_alreadyDispatchedButNoCatchup() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = $now - 12000;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$this->callback(function ($v) use ($now) {
							return $v === $now + 1000;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls(
					$now + 1000,
					null
				);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(0);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(1, $manager->dispatch(6000));

			// upcoming job
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task, $now) {
				return $job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now + 1000 &&
				       $job->delay <= 1000 && $job->delay >= 995;
			});

		}


		public function testDispatch_oneActive_alreadyDispatched_noCatchupRequired() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = $now - 300;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				//->expects($this->exactly(3))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now - 300,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 1000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now - 300,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls(
					$now + 1000,
					$now + 1000,
					null,
					null
				); // no catchup is required because next after last is the same as next after current timestamp


			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(1, $manager->dispatch(6000));

			// upcoming job
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task, $now) {
				return $job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now + 1000 &&
				       $job->delay <= 1000 && $job->delay >= 995;
			});

		}

		public function testDispatch_oneActive_alreadyDispatched_catchupRequired() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = $now - 300;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(5))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now - 300,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls($now + 1000, $now - 150, $now + 1000, null, null); // no catchup is required because next after last is the same as next after current timestamp


			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(2, $manager->dispatch(6000));


			// catchup job
			$l = [
				'a' => 0,
				'b' => 0,
			];
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task, $now, &$l) {
				return ($job instanceof CronJob &&
				        $job->getCronScheduleKey() === 'key1' &&
				        $job->getCronScheduledTs() === $now - 150 &&
				        $job->delay == 0 && ++$l['a'])
					|| ($job instanceof CronJob &&
					    $job->getCronScheduleKey() === 'key1' &&
					    $job->getCronScheduledTs() === $now + 1000 &&
					    $job->delay <= 1000 && $job->delay >= 995 && ++$l['b']);

			});

			$this->assertEquals(['a' => 1, 'b' => 1], $l);

		}


		public function testDispatch_oneActive_alreadyDispatched_multipleCatchupRequired() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = $now - 300;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(6))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now - 300,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls($now + 1000, $now - 150, $now - 100, $now + 1000, null, null); // no catchup is required because next after last is the same as next after current timestamp


			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(3, $manager->dispatch(6000));

			$l = [
				'a' => 0,
				'b' => 0,
				'c' => 0
			];
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task, $now, &$l) {
				return ($job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now - 150 &&
				       $job->delay == 0 && ++$l['a']) || (
					       $job instanceof CronJob &&
					       $job->getCronScheduleKey() === 'key1' &&
					       $job->getCronScheduledTs() === $now - 100 &&
					       $job->delay == 0 && ++$l['b']
				       ) || (
						$job instanceof CronJob &&
						$job->getCronScheduleKey() === 'key1' &&
						$job->getCronScheduledTs() === $now + 1000 &&
						$job->delay <= 1000 && $job->delay >= 995 && ++$l['c']
						)
					;

			});

			$this->assertEquals(['a' => 1, 'b' => 1, 'c' => 1], $l);
		}


		public function testDispatch_oneActive_alreadyDispatched_notAllRequiredCatchupJobsWithinTimeout() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = $now - 300;

			$task = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(6))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now - 300,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls($now + 1000, $now - 150, $now - 100, $now + 1000, null, null); // no catchup is required because next after last is the same as next after current timestamp


			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(120);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(2, $manager->dispatch(6000));

			$l = [
				'a' => 0,
				'b' => 0,
			];
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task, $now, &$l) {
				return ($job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now - 100 &&
				       $job->delay == 0 && ++$l['a']) || ($job instanceof CronJob &&
				                             $job->getCronScheduleKey() === 'key1' &&
				                             $job->getCronScheduledTs() === $now + 1000 &&
				                             $job->delay <= 1000 && $job->delay >= 995 && ++$l['b']);

			});
			$this->assertEquals(['a' => 1, 'b' => 1], $l);

		}

		public function testDispatch_multipleActive_notDispatchedYet() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$task = new CronManagerTestJob();
			$task2 = new CronManagerTestJob();;

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->exactly(3))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 1000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 1000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls($now + 1000, null, null);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);

			$expressionMock2 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock2
				->expects($this->exactly(3))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 2000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 2000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls($now + 2000, null, null);

			$scheduleMock2 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock2
				->method('getExpression')
				->willReturn($expressionMock2);
			$scheduleMock2
				->method('getKey')
				->willReturn('key2');
			$scheduleMock2
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock2
				->method('isActive')
				->willReturn(true);
			$scheduleMock2
				->method('getJob')
				->willReturn($task2);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->withConsecutive(['key1'], ['key1'], ['key2'], ['key2'])
				->willReturnOnConsecutiveCalls(
					$lastScheduled,
					$now + 1000,
					$lastScheduled,
					$now + 2000
				);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
					$scheduleMock2,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(2, $manager->dispatch(6000));

			$l = [
				'a' => 0,
				'b' => 0,
			];
			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task, $now, &$l) {
				return ($job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key1' &&
				       $job->getCronScheduledTs() === $now + 1000 &&
				       $job->delay <= 1000 && $job->delay >= 995 && ++$l['a']) || ($job instanceof CronJob &&
				                                                      $job->getCronScheduleKey() === 'key2' &&
				                                                      $job->getCronScheduledTs() === $now + 2000 &&
				                                                      $job->delay <= 2000 && $job->delay >= 1995 && ++$l['b']);
			});

			$this->assertEquals(['a' => 1, 'b' => 1], $l);

		}

		public function testDispatch_onlySomeActive_notDispatchedYet() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$task = new CronManagerTestJob();
			$task2 = new CronManagerTestJob();

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->expects($this->never())
				->method('nextAfter');

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(false);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);

			$expressionMock2 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock2
				->expects($this->exactly(2))
				->method('nextAfter')
				->withConsecutive(
					[
						$this->callback(function ($v) use ($now) {
							return $now <= $v && $v <= $now + 10;
						}),
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					],
					[
						$now + 2000,
						$this->callback(function ($v) use ($now) {
							return $now + 6000 <= $v && $v <= $now + 6000 + 10;
						})
					]
				)
				->willReturnOnConsecutiveCalls($now + 2000, null);

			$scheduleMock2 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock2
				->method('getExpression')
				->willReturn($expressionMock2);
			$scheduleMock2
				->method('getKey')
				->willReturn('key2');
			$scheduleMock2
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock2
				->method('isActive')
				->willReturn(true);
			$scheduleMock2
				->method('getJob')
				->willReturn($task2);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->withConsecutive(['key2'])
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
					$scheduleMock2,
				]));


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			Queue::fake();

			$this->assertSame(1, $manager->dispatch(6000));

			// upcoming job

			Queue::assertPushed(CronManagerTestJob::class, function ($job) use ($task2, $now) {

				return $job instanceof CronJob &&
				       $job->getCronScheduleKey() === 'key2' &&
				       $job->getCronScheduledTs() === $now + 2000 &&
				       $job->delay <= 2000 && $job->delay >= 1995;

			});
		}


		public function testDispatch_execute() {

			config()->set('cron.scheduleLog', 'testing');
			config()->set('cron.store', 'testing');

			$now = time();

			$lastScheduled = null;

			$cls = $this->getMockBuilder(\MehrIt\LaraCron\Contracts\CronJob::class)->getMock();
			$cls->id = 15;

			$task = new CronManagerTestJob($cls);

			$expressionMock1 = $this->getMockBuilder(CronExpression::class)->disableOriginalConstructor()->getMock();
			$expressionMock1
				->method('nextAfter')
				->with()
				->willReturnOnConsecutiveCalls($now + 1000);

			$scheduleMock1 = $this->getMockBuilder(CronScheduleContract::class)->getMock();
			$scheduleMock1
				->method('getExpression')
				->willReturn($expressionMock1);
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getCatchUpTimeout')
				->willReturn(500);
			$scheduleMock1
				->method('isActive')
				->willReturn(true);
			$scheduleMock1
				->method('getJob')
				->willReturn($task);


			$logMock = $this->getMockBuilder(CronScheduleLog::class)->getMock();
			$logMock
				->method('getLastSchedule')
				->with('key1')
				->willReturn($lastScheduled);

			$storeMock = $this->getMockBuilder(CronStore::class)->getMock();
			$storeMock
				->method('all')
				->with(null)
				->willReturn(new \ArrayIterator([
					$scheduleMock1,
				]));
			$storeMock
				->method('get')
				->with('key1')
				->willReturn($scheduleMock1);


			$manager = new CronManager();
			$manager->registerScheduleLogDriver('testing', function () use ($logMock) {
				return $logMock;
			});
			$manager->registerStoreDriver('testing', function () use ($storeMock) {
				return $storeMock;
			});


			app()->singleton(\MehrIt\LaraCron\Contracts\CronManager::class, function() use ($manager) {
				return $manager;
			});


			$this->assertSame(1, $manager->dispatch(6000));

			// sync queue not should handle the cron job and the task should be invoked

			$this->assertEquals($cls, CronManagerTestJob::$lastResult);
		}


	}

	class CronManagerTestStoreModel extends Model {

	}

	class CronManagerTestScheduleLogModel extends Model {

	}

	class CronManagerTestJob implements CronJob {

		use InteractsWithCron;
		use Queueable;

		public static $lastResult = null;


		protected $res;

		/**
		 * TestTask constructor.
		 * @param $res
		 */
		public function __construct($res = null) {
			$this->res = $res;
		}

		public function handle() {
			static::$lastResult = $this->res;
		}


	}

	class CronManagerTestJob_withoutInteract implements CronJob {

		use Queueable;

		public static $lastResult = null;


		protected $res;

		/**
		 * TestTask constructor.
		 * @param $res
		 */
		public function __construct($res = null) {
			$this->res = $res;
		}

		public function handle() {
			static::$lastResult = $this->res;
		}


	}