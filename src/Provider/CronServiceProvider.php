<?php


	namespace MehrIt\LaraCron\Provider;


	use Illuminate\Console\Scheduling\Schedule;
	use Illuminate\Contracts\Support\DeferrableProvider;
	use Illuminate\Support\ServiceProvider;
	use MehrIt\LaraCron\Command\CronDispatchCommand;
	use MehrIt\LaraCron\Contracts\CronManager;
	use MehrIt\LaraCron\Contracts\CronSchedule;
	use MehrIt\LaraCron\Validation\CronExpressionValidationRule;

	class CronServiceProvider extends ServiceProvider implements DeferrableProvider
	{
		const PACKAGE_NAME = 'cron';

		/**
		 * All of the container singletons that should be registered.
		 *
		 * @var array
		 */
		public $singletons = [
			CronDispatchCommand::class => CronDispatchCommand::class,
			CronManager::class         => \MehrIt\LaraCron\CronManager::class,
			CronSchedule::class        => \MehrIt\LaraCron\CronSchedule::class,
		];

		/**
		 * Bootstrap the application services.
		 *
		 * @return void
		 */
		public function boot() {

			$this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', self::PACKAGE_NAME);

			if ($this->app->runningInConsole()) {

				// register commands
				$this->commands([
					CronDispatchCommand::class
				]);

				// migrations
				$this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

				// publish config and translations
				$this->publishes([
					__DIR__ . '/../../config/config.php' => config_path(self::PACKAGE_NAME . '.php'),
					__DIR__ . '/../../resources/lang' => resource_path('lang/vendor/' . self::PACKAGE_NAME),
				]);

				// schedule cron job dispatch using laravel's dispatcher
				if (config('cron.dispatch_schedule', true)) {

					$this->app->booted(function () {

						/** @var Schedule $schedule */
						$schedule = $this->app->make(Schedule::class);
						$schedule->command(CronDispatchCommand::class, ['period' => 600])->everyFiveMinutes();

					});

				}

			}

		}

		/**
		 * Register the service provider.
		 *
		 * @return void
		 */
		public function register() {
			$this->mergeConfigFrom(__DIR__ . '/../../config/config.php', self::PACKAGE_NAME);
		}

		/**
		 * Get the services provided by the provider.
		 *
		 * @return array
		 */
		public function provides() {
			return [
				CronDispatchCommand::class,
				CronManager::class,
				CronSchedule::class,
				CronExpressionValidationRule::class
			];
		}

	}