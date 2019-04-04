<?php


	namespace MehrIt\LaraCron\Command;


	use Carbon\Carbon;
	use Illuminate\Console\Command;
	use MehrIt\LaraCron\Contracts\CronManager;

	/**
	 * Dispatches all scheduled cron jobs for the given period
	 * @package MehrIt\LaraDynamicSchedules\Command
	 */
	class CronDispatchCommand extends Command
	{
		/**
		 * The name and signature of the console command.
		 *
		 * @var string
		 */
		protected $signature = 'cron:dispatch {period : The period from now on for which to dispatch scheduled cron jobs for (in seconds). This should be greater than the timespan until the next dispatch is invoked.}';

		/**
		 * The console command description.
		 *
		 * @var string
		 */
		protected $description = 'Dispatches all cron jobs which are scheduled for the given period';


		/**
		 * Execute the console command.
		 */
		public function handle(CronManager $manager) {

			$now = time();

			$period = $this->argument('period');

			if ($period <= 0) {
				$this->error("Period must be greater than 0, got $period");
				return 1;
			}

			$dispatchCount = $manager->dispatch($period);

			$this->info("Dispatched $dispatchCount cron job(s).");
			$this->info("Next dispatch should be invoked before " . Carbon::createFromTimestamp($now + $period)->format('Y-m-d H:i:s') . ' (@' . ($now + $period) . ').');


			return 0;
		}


	}