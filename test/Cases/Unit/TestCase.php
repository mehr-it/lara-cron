<?php


	namespace MehrItLaraCronTest\Cases\Unit;


	use MehrIt\LaraCron\Provider\CronServiceProvider;

	abstract class TestCase extends \Orchestra\Testbench\TestCase
	{
		protected $timezone;

		/**
		 * @inheritDoc
		 */
		protected function setUp():void {
			parent::setUp();

			$this->timezone = new \DateTimeZone('Europe/Berlin');

			$this->artisan('migrate')->run();
		}

		protected function getPackageProviders($app) {
			return [
				CronServiceProvider::class,
			];
		}

		/**
		 * Define environment setup.
		 *
		 * @param \Illuminate\Foundation\Application $app
		 * @return void
		 */
		protected function getEnvironmentSetUp($app) {
			// Setup default database to use sqlite :memory:
			$app['config']->set('database.connections.testing', [
				'driver'   => 'sqlite',
				'database' => ':memory:',
				'prefix'   => '',
			]);
		}

		/**
		 * Mocks an instance in the application service container
		 * @param string $instance The instance to mock
		 * @param string|null $mockedClass The class to use for creating a mock object. Null to use same as $instance
		 * @return \PHPUnit\Framework\MockObject\MockObject
		 */
		protected function mockAppSingleton($instance, $mockedClass = null) {

			if (!$mockedClass)
				$mockedClass = $instance;

			$mock = $this->getMockBuilder($mockedClass)->disableOriginalConstructor()->getMock();
			app()->singleton($instance, function () use ($mock) {
				return $mock;
			});

			return $mock;
		}

	}