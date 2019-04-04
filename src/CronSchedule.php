<?php


	namespace MehrIt\LaraCron;


	use MehrIt\LaraCron\Contracts\CronExpression;
	use MehrIt\LaraCron\Contracts\CronJob;
	use MehrIt\LaraCron\Contracts\CronSchedule as CronScheduleContract;

	/**
	 * Represents a scheduled cron jobs
	 * @package MehrIt\LaraDynamicSchedules
	 */
	class CronSchedule implements CronScheduleContract
	{
		/**
		 * @var CronExpression
		 */
		protected $expression;

		/**
		 * @var mixed
		 */
		protected $job;

		/**
		 * @var bool
		 */
		protected $active;

		/**
		 * @var string
		 */
		protected $key;

		/**
		 * @var string|null
		 */
		protected $group;

		/**
		 * @var int
		 */
		protected $catchupTimeout;

		/**
		 * Creates a new instance
		 * @param CronExpression $expression The cron expression
		 * @param CronJob $job The job to run
		 * @param string $key The key identifying the schedule
		 * @param string|null $group The group name
		 * @param bool $active True if active. Else false.
		 * @param int $catchupTimeout The catchup timeout.
		 */
		public function __construct(CronExpression $expression, CronJob $job, string $key, string $group = null, bool $active = true, int $catchupTimeout = 0) {
			$this->expression     = $expression;
			$this->job            = $job;
			$this->active         = $active;
			$this->key            = $key;
			$this->group          = $group;
			$this->catchupTimeout = $catchupTimeout;
		}


		/**
		 * @inheritDoc
		 */
		public function getExpression(): CronExpression {
			return $this->expression;
		}

		/**
		 * @inheritDoc
		 */
		public function setExpression(CronExpression $expression): CronScheduleContract {
			$this->expression = $expression;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getJob() : CronJob {
			return $this->job;
		}

		/**
		 * @inheritDoc
		 */
		public function setJob(CronJob $job): CronScheduleContract {
			$this->job = $job;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function isActive(): bool {
			return $this->active;
		}

		/**
		 * @inheritDoc
		 */
		public function setActive(bool $active): CronScheduleContract {
			$this->active = $active;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getKey(): string {
			return $this->key;
		}

		/**
		 * @inheritDoc
		 */
		public function setKey(string $key): CronScheduleContract {
			$this->key = $key;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getGroup(): ?string {
			return $this->group;
		}

		/**
		 * @inheritDoc
		 */
		public function setGroup(?string $group): CronScheduleContract {
			$this->group = $group;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getCatchupTimeout(): int {
			return $this->catchupTimeout;
		}

		/**
		 * @inheritDoc
		 */
		public function setCatchupTimeout(int $catchupTimeout): CronScheduleContract {
			$this->catchupTimeout = $catchupTimeout;

			return $this;
		}



	}