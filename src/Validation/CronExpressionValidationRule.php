<?php


	namespace MehrIt\LaraCron\Validation;


	use Illuminate\Contracts\Validation\Rule;
	use MehrIt\LaraCron\Cron\CronExpression;
	use MehrIt\LaraCron\Cron\Exception\InvalidCronExpressionException;
	use MehrIt\LaraCron\Cron\Exception\InvalidCronFieldExpressionException;
	use MehrIt\LaraCron\Provider\CronServiceProvider;

	class CronExpressionValidationRule implements Rule
	{
		/**
		 * @inheritDoc
		 */
		public function passes($attribute, $value) {
			try {
				// we simply create a cron, and try a match operation
				(new CronExpression($value, 'UTC'))->matches(0);

				return true;
			}
			catch(InvalidCronFieldExpressionException $ex) {
				return false;
			}
			catch (InvalidCronExpressionException $ex) {
				return false;
			}
		}

		/**
		 * @inheritDoc
		 */
		public function message() {
			return trans(CronServiceProvider::PACKAGE_NAME . '::validation.cronExpression');
		}

	}