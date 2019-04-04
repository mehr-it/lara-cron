<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 06.02.19
	 * Time: 17:11
	 */

	namespace MehrIt\LaraCron\Cron\Field\Expression;


	interface FieldExpression
	{
		/**
		 * Returns if the given value matches the expression
		 * @param int $value The value
		 * @return bool True if matching. Else false.
		 */
		public function isMatching(int $value): bool;

		/**
		 * Returns the next matching value after given value
		 * @param int $value The value
		 * @return int The next value after
		 * @throws \MehrIt\LaraCron\Cron\Exception\OutOfRangeException
		 */
		public function nextAfter(int $value): int;
	}