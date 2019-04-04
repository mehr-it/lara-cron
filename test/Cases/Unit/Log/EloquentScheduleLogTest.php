<?php


	namespace MehrItLaraCronTest\Cases\Unit\Log;


	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrIt\LaraCron\Log\CronScheduleLogEntry;
	use MehrIt\LaraCron\Log\EloquentScheduleLog;
	use MehrItLaraCronTest\Cases\Unit\TestCase;

	class EloquentScheduleLogTest extends TestCase
	{
		use DatabaseTransactions;

		protected function createLog() {
			return new EloquentScheduleLog(CronScheduleLogEntry::class);
		}

		public function testGetModel() {

			$store = new EloquentScheduleLog(EloquentScheduleLogTestModel::class);

			$this->assertSame(EloquentScheduleLogTestModel::class, $store->getModel());
		}

		public function testGetLastSchedule_notLogged() {

			$log = $this->createLog();

			$log->log('key1', time());

			$this->assertNull($log->getLastSchedule('key2'));

			// we also test with new instance
			$newInstance = $this->createLog();
			$this->assertNull($newInstance->getLastSchedule('key2'));
		}

		public function testGetLastSchedule() {

			$log = $this->createLog();

			$now = time();

			$log->log('key1', $now - 10);
			$log->log('key2', $now - 100);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			// we also test with new instance
			$newInstance = $this->createLog();
			$this->assertSame($now - 10, $newInstance->getLastSchedule('key1'));
			$this->assertSame($now - 100, $newInstance->getLastSchedule('key2'));
		}

		public function testLog_alreadyExistingButLater() {
			$log = $this->createLog();

			$now = time();

			$log->log('key1', $now - 10);
			$log->log('key2', $now - 100);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			$log->log('key1', $now - 5);

			$this->assertSame($now - 5, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			// we also test with new instance
			$newInstance = $this->createLog();
			$this->assertSame($now - 5, $newInstance->getLastSchedule('key1'));
			$this->assertSame($now - 100, $newInstance->getLastSchedule('key2'));
		}

		public function testLog_alreadyExistingButBefore() {
			$log = $this->createLog();

			$now = time();

			$log->log('key1', $now - 10);
			$log->log('key2', $now - 100);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			$log->log('key1', $now - 20);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			// we also test with new instance
			$newInstance = $this->createLog();
			$this->assertSame($now - 10, $newInstance->getLastSchedule('key1'));
			$this->assertSame($now - 100, $newInstance->getLastSchedule('key2'));
		}

	}

	class EloquentScheduleLogTestModel extends Model {

	}