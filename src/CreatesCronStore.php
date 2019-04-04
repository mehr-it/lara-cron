<?php


	namespace MehrIt\LaraCron;


	use MehrIt\LaraCron\Contracts\CronStore;
	use MehrIt\LaraCron\Store\CronTabEntry;
	use MehrIt\LaraCron\Store\EloquentStore;
	use MehrIt\LaraCron\Store\MemoryStore;

	/**
	 * Creates cron store instance
	 * @package MehrIt\LaraDynamicSchedules
	 */
	trait CreatesCronStore
	{
		protected $customStoreCreators = [];

		/**
		 * Creates a new cron store instance
		 * @param array|string $config The store configuration or driver name
		 * @return CronStore The store instance
		 */
		protected function makeStore($config) : CronStore {

			// interpret strings as driver name
			if (!is_array($config)) {
				$config = [
					'driver' => $config
				];
			}

			// default driver
			if (!($config['driver'] ?? null))
				$config['driver'] = 'eloquent';


			// call custom creator if existing
			if ($this->customStoreCreators[$config['driver']] ?? null)
				return $this->callCustomStoreCreator($config['driver'], $config);


			// create built-in stores
			switch($config['driver']) {
				case 'memory':
					return $this->createMemoryStore($config);

				case 'eloquent':
					return $this->createEloquentStore($config);

				default:
					throw new \RuntimeException('Unknown cron store driver "' . $config['driver'] .'"');
			}

		}

		/**
		 * Calls a custom store creator
		 * @param string $driver The driver name
		 * @param array $config The store configuration
		 * @return CronStore The store instance
		 */
		protected function callCustomStoreCreator(string $driver, array $config) : CronStore {
			$store = call_user_func($this->customStoreCreators[$driver], $config);

			if (!($store instanceof CronStore))
				throw new \RuntimeException("Custom store creator for driver $driver must return instance of " . CronStore::class . ', got ' . get_class($store));

			return $store;
		}

		/**
		 * Creates a new memory store
		 * @param array $config The config
		 * @return MemoryStore The instance
		 */
		protected function createMemoryStore(array $config) : MemoryStore {
			return new MemoryStore();
		}

		/**
		 * Creates a new eloquent store
		 * @param array $config The config
		 * @return EloquentStore The instance
		 */
		protected function createEloquentStore(array $config) : EloquentStore {

			$model = ($config['model'] ?? null) ?: CronTabEntry::class;

			return new EloquentStore($model);
		}


		/**
		 * Registers a custom store driver
		 * @param string $name The driver name
		 * @param callable $resolver The resolver function which returns the store instance. Receives the configuration array as argument
		 * @return $this
		 */
		public function registerStoreDriver(string $name, callable $resolver) {
			$this->customStoreCreators[$name] = $resolver;

			return $this;
		}


	}