<?php


	namespace MehrIt\LaraCron\Log;


	use Illuminate\Database\Eloquent\Model;

	class CronScheduleLogEntry extends Model
	{
		protected $primaryKey = 'key';

		public $incrementing = false;
	}