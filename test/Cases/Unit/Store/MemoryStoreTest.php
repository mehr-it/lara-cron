<?php


	namespace MehrItLaraCronTest\Cases\Unit\Store;


	use MehrIt\LaraCron\Contracts\CronSchedule;
	use MehrIt\LaraCron\Store\MemoryStore;
	use MehrItLaraCronTest\Cases\Unit\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class MemoryStoreTest extends TestCase
	{

		public function testGet_notExisting() {

			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');


			$this->assertSame(null, $store->get('key2'));

		}

		public function testGetPut() {

			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');

			/** @var CronSchedule|MockObject $scheduleMock2 */
			$scheduleMock2 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock2
				->method('getKey')
				->willReturn('key2');

			$this->assertSame($store, $store->put($scheduleMock1));
			$this->assertSame($store, $store->put($scheduleMock2));

			$this->assertSame($scheduleMock1, $store->get('key1'));
			$this->assertSame($scheduleMock2, $store->get('key2'));

		}

		public function testPut_overwrite() {
			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');

			/** @var CronSchedule|MockObject $scheduleMock2 */
			$scheduleMock2 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock2
				->method('getKey')
				->willReturn('key1');

			$this->assertSame($store, $store->put($scheduleMock1));
			$this->assertSame($store, $store->put($scheduleMock2));

			$this->assertSame($scheduleMock2, $store->get('key1'));

		}

		public function testAll() {

			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');

			/** @var CronSchedule|MockObject $scheduleMock2 */
			$scheduleMock2 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock2
				->method('getKey')
				->willReturn('key2');

			$this->assertSame($store, $store->put($scheduleMock1));
			$this->assertSame($store, $store->put($scheduleMock2));

			$ret = $store->all();

			$this->assertContains($scheduleMock1, $ret);
			$this->assertContains($scheduleMock2, $ret);

		}

		public function testAll_withGroupFilter() {

			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');
			$scheduleMock1
				->method('getGroup')
				->willReturn('group1');

			/** @var CronSchedule|MockObject $scheduleMock2 */
			$scheduleMock2 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock2
				->method('getKey')
				->willReturn('key2');

			/** @var CronSchedule|MockObject $scheduleMock3 */
			$scheduleMock3 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock3
				->method('getKey')
				->willReturn('key3');
			$scheduleMock3
				->method('getGroup')
				->willReturn('group1');

			$this->assertSame($store, $store->put($scheduleMock1));
			$this->assertSame($store, $store->put($scheduleMock2));
			$this->assertSame($store, $store->put($scheduleMock3));

			// without group
			$ret = iterator_to_array($store->all());
			$this->assertContains($scheduleMock1, $ret);
			$this->assertContains($scheduleMock2, $ret);
			$this->assertContains($scheduleMock3, $ret);

			// with group
			$ret = iterator_to_array($store->all('group1'));
			$this->assertContains($scheduleMock1, $ret);
			$this->assertNotContains($scheduleMock2, $ret);
			$this->assertContains($scheduleMock3, $ret);

		}

		public function testAll_empty() {

			$store = new MemoryStore();

			$this->assertEmpty(iterator_to_array($store->all()));

		}

		public function testDelete() {
			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');

			/** @var CronSchedule|MockObject $scheduleMock2 */
			$scheduleMock2 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock2
				->method('getKey')
				->willReturn('key2');

			$this->assertSame($store, $store->put($scheduleMock1));
			$this->assertSame($store, $store->put($scheduleMock2));

			$this->assertSame($scheduleMock1, $store->get('key1'));
			$this->assertSame($scheduleMock2, $store->get('key2'));

			$this->assertSame($store, $store->delete('key1'));

			$this->assertSame(null, $store->get('key1'));
			$this->assertSame($scheduleMock2, $store->get('key2'));

		}

		public function testDelete_notExisting() {
			$store = new MemoryStore();

			/** @var CronSchedule|MockObject $scheduleMock1 */
			$scheduleMock1 = $this->getMockBuilder(CronSchedule::class)->getMock();
			$scheduleMock1
				->method('getKey')
				->willReturn('key1');


			$this->assertSame($store, $store->put($scheduleMock1));

			$this->assertSame($scheduleMock1, $store->get('key1'));

			$this->assertSame($store, $store->delete('key2'));

			$this->assertSame($scheduleMock1, $store->get('key1'));

		}

	}

