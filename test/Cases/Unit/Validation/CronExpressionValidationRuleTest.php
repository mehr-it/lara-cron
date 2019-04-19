<?php


	namespace MehrItLaraCronTest\Cases\Unit\Validation;



	use Illuminate\Contracts\Validation\Validator;
	use MehrIt\LaraCron\Validation\CronExpressionValidationRule;
	use MehrItLaraCronTest\Cases\Unit\TestCase;

	class CronExpressionValidationRuleTest extends TestCase
	{

		protected function check($expresion) {

			/** @var Validator $validator */
			$validator = \Validator::make(
				[
					'field' => $expresion
				],
				[
					'field' => new CronExpressionValidationRule()
				]
			);

			return $validator->errors()->get('field')[0] ?? true;

		}

		public function testValid() {

			$this->assertSame(true, $this->check('* 5 * * *'));

		}

		public function testInvalid_hour() {

			$this->assertSame('Cron expression is invalid', $this->check('* 65 * * *'));

		}
		public function testInvalid_NumberOfFields() {

			$this->assertSame('Cron expression is invalid', $this->check('* 1 * *'));

		}

	}