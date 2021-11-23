<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 04.02.19
	 * Time: 20:56
	 */

	namespace MehrIt\LaraCron\Cron\Exception;


	use Throwable;

	class InvalidCronFieldExpressionException extends \InvalidArgumentException
	{

		protected $field;

		protected $expression;

		/**
		 * Construct the exception. Note: The message is NOT binary safe.
		 * @link https://php.net/manual/en/exception.construct.php
		 * @param string $expression The field expression
		 * @param string $field The field name
		 * @param string $message [optional] The Exception message to throw.
		 * @param int $code [optional] The Exception code.
		 * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
		 * @since 5.1.0
		 */
		public function __construct(string $expression, string $field, string $message = "", int $code = 0, Throwable $previous = null) {

			$this->expression = $expression;
			$this->field      = $field;


			if (!$message)
				$message = "Expression \"$expression\" is invalid for field $field";

			parent::__construct($message, $code, $previous);
		}

		/**
		 * Gets the field name
		 * @return string The field name
		 */
		public function getField(): string {
			return $this->field;
		}

		/**
		 * Gets the expression
		 * @return string The expression
		 */
		public function getExpression(): string {
			return $this->expression;
		}



	}