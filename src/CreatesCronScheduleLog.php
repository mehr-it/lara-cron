<?php


	namespace MehrIt\LaraCron;


	use MehrIt\LaraCron\Contracts\CronScheduleLog;
	use MehrIt\LaraCron\Log\CronScheduleLogEntry;
	use MehrIt\LaraCron\Log\EloquentScheduleLog;
	use MehrIt\LaraCron\Log\MemoryScheduleLog;

	trait CreatesCronScheduleLog
	{
		protected $customLogCreators = [];

		/**
		 * Creates a new cron schedule log instance
		 * @param array|string $config The log configuration or driver name
		 * @return CronScheduleLog The log instance
		 */
		protected function makeScheduleLog($config): CronScheduleLog {

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
			if ($this->customLogCreators[$config['driver']] ?? null)
				return $this->callCustomLogCreator($config['driver'], $config);


			// create built-in logs
			switch ($config['driver']) {
				case 'memory':
					return $this->createMemoryLog($config);

				case 'eloquent':
					return $this->createEloquentLog($config);

				default:
					throw new \RuntimeException('Unknown schedule log driver "' . $config['driver'] . '"');
			}

		}

		/**
		 * Calls a custom log creator
		 * @param string $driver The driver name
		 * @param array $config The log configuration
		 * @return CronScheduleLog The log instance
		 */
		protected function callCustomLogCreator(string $driver, array $config): CronScheduleLog {
			$log = call_user_func($this->customLogCreators[$driver], $config);

			if (!($log instanceof CronScheduleLog))
				throw new \RuntimeException("Custom schedule log creator for driver $driver must return instance of " . CronScheduleLog::class . ', got ' . get_class($log));

			return $log;
		}

		/**
		 * Creates a new memory log
		 * @param array $config The config
		 * @return MemoryScheduleLog The instance
		 */
		protected function createMemoryLog(array $config): MemoryScheduleLog {
			return new MemoryScheduleLog();
		}

		/**
		 * Creates a new eloquent log
		 * @param array $config The config
		 * @return EloquentScheduleLog The instance
		 */
		protected function createEloquentLog(array $config): EloquentScheduleLog {

			$model = ($config['model'] ?? null) ?: CronScheduleLogEntry::class;

			return new EloquentScheduleLog($model);
		}


		/**
		 * Registers a custom schedule log driver
		 * @param string $name The driver name
		 * @param callable $resolver The resolver function which returns the log instance. Receives the configuration array as argument
		 * @return $this
		 */
		public function registerScheduleLogDriver(string $name, callable $resolver) {
			$this->customLogCreators[$name] = $resolver;

			return $this;
		}
	}