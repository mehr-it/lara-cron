<?php


	namespace MehrIt\LaraCron\Store;


	use DateTimeZone;
	use Exception;
	use Illuminate\Database\Eloquent\Model;
	use MehrIt\LaraCron\Contracts\CronSchedule;
	use MehrIt\LaraCron\Contracts\CronStore;
	use MehrIt\LaraCron\Cron\CronExpression;
	use MehrIt\LaraCron\CronSchedule as CronScheduleObject;
	use RuntimeException;
	use Traversable;

	/**
	 * Stores cron schedules using eloquent models
	 * @package MehrIt\LaraDynamicSchedules\Store
	 */
	class EloquentStore implements CronStore
	{
		/**
		 * @var string
		 */
		protected $model;

		/**
		 * Creates a new instance
		 * @param string $model The model class
		 */
		public function __construct(string $model) {
			$this->model = $model;
		}

		/**
		 * Gets the model class
		 * @return string The model class
		 */
		public function getModel(): string {
			return $this->model;
		}


		/**
		 * @inheritDoc
		 */
		public function get(string $key): ?CronSchedule {
			return $this->scheduleFromRecord($this->fetchRecord($key));
		}

		/**
		 * @inheritDoc
		 */
		public function all(string $group = null): Traversable {

			$query = $this->createModel()->newQuery();

			// filter by group if passed
			if ($group !== null)
				$query->where('group', $group);

			foreach($query->cursor() as $currRecord) {

				yield $this->scheduleFromRecord($currRecord);
			}
		}

		/**
		 * @inheritDoc
		 */
		public function put(CronSchedule $schedule): CronStore {
			$record = $this->recordFromSchedule($schedule, $this->fetchRecord($schedule->getKey()));

			$record->save();

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function delete(string $key): CronStore {
			// delete schedules
			$this->createModel()->newQuery()->where('key', $key)->delete();

			return $this;
		}

		/**
		 * Creates a schedule from the given record
		 * @param Model|null $record The record or null
		 * @return CronSchedule|null The schedule or null
		 */
		protected function scheduleFromRecord(?Model $record) : ?CronSchedule {
			// return null if not existing
			if (!$record)
				return null;

			// create timezone
			try {
				$timezone = new DateTimeZone($record->timezone ?: date_default_timezone_get());
			}
			catch (Exception $ex) {
				throw new RuntimeException("Unknown timezone \"{$record->timezone}\" for cron schedule {$record->key}.");
			}

			// unserialize job
			$job = unserialize($record->job);

			return new CronScheduleObject(
				new CronExpression($record->expression, $timezone),
				$job,
				$record->key,
				$record->group,
				$record->active,
				$record->catchup_timeout
			);
		}

		/**
		 * Creates or updates a record with given schedule data
		 * @param CronSchedule $schedule The schedule
		 * @param Model|null $existingRecord The record to update if any. Else a new record is created
		 * @return Model The record
		 */
		protected function recordFromSchedule(CronSchedule $schedule, Model $existingRecord = null) {


			$record = $existingRecord ?: $this->createModel();

			$cronExpression = $schedule->getExpression();

			$record->key = $schedule->getKey();
			$record->group = $schedule->getGroup();
			$record->timezone = $cronExpression->getTimezone()->getName();
			$record->expression = $cronExpression->getExpression();
			$record->active = $schedule->isActive();
			$record->catchup_timeout = $schedule->getCatchUpTimeout();
			$record->job = serialize(clone $schedule->getJob());

			return $record;

		}

		/**
		 * Fetches the record for the given schedule key
		 * @param string $key The schedule key
		 * @return Model|null The record or null if not existing
		 */
		protected function fetchRecord(string $key) : ?Model {
			return $this->createModel()->newQuery()
				->where('key', $key)
				->first();
		}

		/**
		 * Create a new instance of the model.
		 *
		 * @return \Illuminate\Database\Eloquent\Model
		 */
		protected function createModel() {
			$class = '\\' . ltrim($this->model, '\\');

			return new $class;
		}


	}