<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 03.02.19
	 * Time: 23:13
	 */

	namespace MehrItLaraCronTest\Cases\Unit;


	abstract class CronTestCase extends \PHPUnit\Framework\TestCase
	{
		protected $timezone;

		/**
		 * @inheritDoc
		 */
		protected function setUp() {
			parent::setUp();

			$this->timezone = new \DateTimeZone('Europe/Berlin');

			date_default_timezone_set('Europe/Berlin');
		}


	}