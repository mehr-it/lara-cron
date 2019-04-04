<?php

	use Illuminate\Support\Facades\Schema;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Database\Migrations\Migration;

	class CreateCronScheduleLogEntriesTable extends Migration
	{
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {
			Schema::create('cron_schedule_log_entries', function (Blueprint $table) {
				$table->string('key', 128);
				$table->integer('last_scheduled_for');
				$table->primary('key');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists('cron_schedule_log_entries');
		}
	}