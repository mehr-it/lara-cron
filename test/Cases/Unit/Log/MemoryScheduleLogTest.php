<?php


	namespace MehrItLaraCronTest\Cases\Unit\Log;


	use MehrIt\LaraCron\Log\MemoryScheduleLog;
	use MehrItLaraCronTest\Cases\Unit\TestCase;

	class MemoryScheduleLogTest extends TestCase
	{

		public function testGetLastSchedule_notLogged() {

			$log = new MemoryScheduleLog();

			$log->log('key1', time());

			$this->assertNull($log->getLastSchedule('key2'));
		}

		public function testGetLastSchedule() {

			$log = new MemoryScheduleLog();

			$now = time();

			$log->log('key1', $now - 10);
			$log->log('key2', $now - 100);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));
		}

		public function testLog_alreadyExistingButLater() {
			$log = new MemoryScheduleLog();

			$now = time();

			$log->log('key1', $now - 10);
			$log->log('key2', $now - 100);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			$log->log('key1', $now - 5);

			$this->assertSame($now - 5, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));
		}

		public function testLog_alreadyExistingButBefore() {
			$log = new MemoryScheduleLog();

			$now = time();

			$log->log('key1', $now - 10);
			$log->log('key2', $now - 100);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));

			$log->log('key1', $now - 20);

			$this->assertSame($now - 10, $log->getLastSchedule('key1'));
			$this->assertSame($now - 100, $log->getLastSchedule('key2'));
		}

		public function testWithScheduleLocked() {

			$log = new MemoryScheduleLog();

			$log->log('key1', time() - 10);


			$ret = new \stdClass();

			$this->assertSame($ret, $log->withScheduleLocked('key1', function () use ($ret) {
				return $ret;
			}));

		}

		public function testWithScheduleLocked_notExisting() {

			$log = new MemoryScheduleLog();

			$ret = new \stdClass();

			$this->assertSame($ret, $log->withScheduleLocked('key1', function () use ($ret) {
				return $ret;
			}));

		}

	}