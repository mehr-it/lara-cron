<?php

	use Illuminate\Support\Facades\Schema;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Database\Migrations\Migration;

	class CreateCronTabEntriesTable extends Migration
	{
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up() {
			Schema::create('cron_tab_entries', function (Blueprint $table) {
				$table->string('key', 128);
				$table->string('group', 128)->nullable();
				$table->string('expression', 128);
				$table->string('timezone', 128);
				$table->boolean('active');
				$table->integer('catchup_timeout');
				$table->binary('job');
				$table->primary('key');
				$table->index('group');
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down() {
			Schema::dropIfExists('cron_tab_entries');
		}
	}