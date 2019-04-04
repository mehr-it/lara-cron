<?php


	namespace MehrItLaraCronTest\Cases\Unit\Store;


	use Illuminate\Bus\Queueable;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Foundation\Testing\DatabaseTransactions;
	use MehrIt\LaraCron\Contracts\CronJob;
	use MehrIt\LaraCron\Contracts\CronSchedule;
	use MehrIt\LaraCron\Cron\CronExpression;
	use MehrIt\LaraCron\Queue\InteractsWithCron;
	use MehrIt\LaraCron\Store\CronTabEntry;
	use MehrIt\LaraCron\Store\EloquentStore;
	use MehrItLaraCronTest\Cases\Unit\TestCase;

	class EloquentStoreTest extends TestCase
	{
		use DatabaseTransactions;


		protected function assertScheduleEquals(CronSchedule $a, CronSchedule $b) {

			if (!(
				$a->getKey() == $b->getKey() &&
				$a->getGroup() == $b->getGroup() &&
				$a->isActive() == $b->isActive() &&
				$a->getJob() == $b->getJob() &&
				$a->getCatchUpTimeout() == $b->getCatchUpTimeout() &&
				$a->getExpression()->getExpression() == $b->getExpression()->getExpression() &&
				$a->getExpression()->getTimezone()->getName() == $b->getExpression()->getTimezone()->getName()
			)) {
				$this->fail('Failed asserting two schedules are equal');
			}

		}

		protected function createStore() {
			return new EloquentStore(CronTabEntry::class);
		}

		public function testGetModel() {

			$store = new EloquentStore(EloquentStoreTestModel::class);

			$this->assertSame(EloquentStoreTestModel::class, $store->getModel());
		}


		public function testGet_notExisting() {
			$store = $this->createStore();

			$store->put(new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1'
			));

			$this->assertNull($store->get('key2'));
		}

		public function testGetPut() {
			$store = $this->createStore();

			$schedule1 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1'
			);
			$schedule2 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key2',
				'my-group',
				false,
				15 * 60
			);

			$this->assertSame($store, $store->put($schedule1));
			$this->assertSame($store, $store->put($schedule2));

			$this->assertScheduleEquals($schedule2, $store->get('key2'));
			$this->assertScheduleEquals($schedule1, $store->get('key1'));


			// we also test with different store instance
			$newStoreInstance = $this->createStore();
			$this->assertScheduleEquals($schedule2, $newStoreInstance->get('key2'));
			$this->assertScheduleEquals($schedule1, $newStoreInstance->get('key1'));
		}

		public function testPut_overwrite() {
			$store = $this->createStore();

			$schedule1 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1'
			);
			$schedule2 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1',
				'my-group',
				false,
				15 * 60
			);

			$this->assertSame($store, $store->put($schedule1));
			$this->assertSame($store, $store->put($schedule2));

			$this->assertScheduleEquals($schedule2, $store->get('key1'));


			// we also test with different store instance
			$newStoreInstance = $this->createStore();
			$this->assertScheduleEquals($schedule2, $newStoreInstance->get('key1'));
		}


		public function testAll() {
			$store = $this->createStore();

			$schedule1 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1'
			);
			$schedule2 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key2',
				'my-group',
				false,
				15 * 60
			);

			$this->assertSame($store, $store->put($schedule1));
			$this->assertSame($store, $store->put($schedule2));

			$ret = iterator_to_array($store->all());

			usort($ret, function(CronSchedule $a, CronSchedule $b) {
				return $a->getKey() <=> $b->getKey();
			});

			$this->assertScheduleEquals($schedule1, $ret[0]);
			$this->assertScheduleEquals($schedule2, $ret[1]);



			// we also test with different store instance
			$newStoreInstance = $this->createStore();
			$ret = iterator_to_array($newStoreInstance->all());
			usort($ret, function (CronSchedule $a, CronSchedule $b) {
				return $a->getKey() <=> $b->getKey();
			});
			$this->assertScheduleEquals($schedule1, $ret[0]);
			$this->assertScheduleEquals($schedule2, $ret[1]);
		}

		public function testAll_withGroupFilter() {
			$store = $this->createStore();

			$schedule1 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1',
				'group1'
			);
			$schedule2 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key2'
			);

			$schedule3 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key3',
				'group1',
				false,
				15 * 60
			);

			$this->assertSame($store, $store->put($schedule1));
			$this->assertSame($store, $store->put($schedule2));
			$this->assertSame($store, $store->put($schedule3));


			// without group
			$ret = iterator_to_array($store->all());

			usort($ret, function (CronSchedule $a, CronSchedule $b) {
				return $a->getKey() <=> $b->getKey();
			});

			$this->assertScheduleEquals($schedule1, $ret[0]);
			$this->assertScheduleEquals($schedule2, $ret[1]);
			$this->assertScheduleEquals($schedule3, $ret[2]);
			$this->assertCount(3, $ret);


			// with group
			$ret = iterator_to_array($store->all('group1'));

			usort($ret, function(CronSchedule $a, CronSchedule $b) {
				return $a->getKey() <=> $b->getKey();
			});

			$this->assertScheduleEquals($schedule1, $ret[0]);
			$this->assertScheduleEquals($schedule3, $ret[1]);
			$this->assertCount(2, $ret);



			// we also test with different store instance
			$newStoreInstance = $this->createStore();
			$ret = iterator_to_array($newStoreInstance->all('group1'));
			usort($ret, function (CronSchedule $a, CronSchedule $b) {
				return $a->getKey() <=> $b->getKey();
			});
			$this->assertScheduleEquals($schedule1, $ret[0]);
			$this->assertScheduleEquals($schedule3, $ret[1]);
			$this->assertCount(2, $ret);
		}

		public function testAll_empty() {
			$store = $this->createStore();

			$ret = iterator_to_array($store->all());

			$this->assertEmpty($ret);
		}

		public function testDelete() {
			$store = $this->createStore();

			$schedule1 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1'
			);
			$schedule2 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key2'
			);

			$this->assertSame($store, $store->put($schedule1));
			$this->assertSame($store, $store->put($schedule2));

			$this->assertScheduleEquals($schedule2, $store->get('key2'));
			$this->assertScheduleEquals($schedule1, $store->get('key1'));

			$this->assertSame($store, $store->delete('key1'));

			$this->assertSame(null, $store->get('key1'));
			$this->assertScheduleEquals($schedule2, $store->get('key2'));


			// we also test with different store instance
			$newStoreInstance = $this->createStore();
			$this->assertSame(null, $newStoreInstance->get('key1'));
			$this->assertScheduleEquals($schedule2, $newStoreInstance->get('key2'));
		}


		public function testDelete_notExisting() {
			$store = $this->createStore();

			$schedule1 = new \MehrIt\LaraCron\CronSchedule(
				new CronExpression('* * * * *', $this->timezone),
				new EloquentStoreTestJob(17),
				'key1'
			);


			$this->assertSame($store, $store->put($schedule1));

			$this->assertScheduleEquals($schedule1, $store->get('key1'));

			$this->assertSame($store, $store->delete('key2'));

			$this->assertScheduleEquals($schedule1, $store->get('key1'));


			// we also test with different store instance
			$newStoreInstance = $this->createStore();
			$this->assertScheduleEquals($schedule1, $newStoreInstance->get('key1'));
		}

	}


	class EloquentStoreTestModel extends Model {

	}

	class EloquentStoreTestJob implements CronJob
	{

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