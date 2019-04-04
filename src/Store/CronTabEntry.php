<?php


	namespace MehrIt\LaraCron\Store;


	use Illuminate\Database\Eloquent\Model;

	class CronTabEntry extends Model
	{

		protected $primaryKey = 'key';

		public $incrementing = false;

	}