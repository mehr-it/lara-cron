<?php


	namespace MehrIt\LaraCron\Log;


	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Query\Builder;
	use MehrIt\LaraCron\Contracts\CronScheduleLog;

	/**
	 * Schedule log using eloquent models
	 * @package MehrIt\LaraDynamicSchedules\Log
	 */
	class EloquentScheduleLog implements CronScheduleLog
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
		public function log(string $key, int $scheduledFor): CronScheduleLog {

			/** @noinspection PhpUnhandledExceptionInspection */
			$this->createModel()->getConnection()->transaction(function() use ($key, $scheduledFor) {

				// fetch existing
				$existing = $this->fetchRecord($key, true); // we need to lock, because we might update later on
				$last = $this->fromRecord($existing);

				// if newer, update record
				if ($last === null || $scheduledFor > $last) {
					$record = $this->toRecord($key, $scheduledFor, $existing);

					$record->save();
				}

			});

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getLastSchedule(string $key): ?int {
			return $this->fromRecord($this->fetchRecord($key));
		}

		/**
		 * Gets the last scheduled time from given record
		 * @param Model|null $record The record or null
		 * @return int|null The last scheduled timestamp or null
		 */
		protected function fromRecord(?Model $record) : ?int {
			if (!$record)
				return null;

			return $record->last_scheduled_for ?: null;
		}

		/**
		 * Creates or updates a record with given log data
		 * @param string $key The schedule key
		 * @param int $scheduledFor The scheduled timestamp
		 * @param Model|null $existingRecord The existing record to be updated. If empty, a new record is created
		 * @return Model The record
		 */
		protected function toRecord(string $key, int $scheduledFor, Model $existingRecord = null) {

			$record = $existingRecord ?: $this->createModel();

			$record->key                = $key;
			$record->last_scheduled_for = $scheduledFor;

			return $record;
		}

		/**
		 * Fetches the record for the given schedule key
		 * @param string $key The schedule key
		 * @param bool $lock True if to lock the record for update
		 * @return Model|null The record or null if not existing
		 */
		protected function fetchRecord(string $key, bool $lock = false): ?Model {
			return $this->createModel()->newQuery()
				->where('key', $key)
				->when($lock, function($query) {
					/** @var Builder $query */

					return $query->lockForUpdate();
				})
				->first();
		}

		/**
		 * Create a new instance of the model
		 * @return Model
		 */
		protected function createModel() {
			$class = '\\' . ltrim($this->model, '\\');

			return new $class;
		}

		/**
		 * @inheritDoc
		 */
		public function withScheduleLocked(string $key, callable $callback) {

			// open a transaction so database locks are possible
			return $this->createModel()->getConnection()->transaction(function() use ($key, $callback) {

				// fetch the record so it gets locked
				$this->fetchRecord($key, true);

				return call_user_func($callback);
			});

		}

	}