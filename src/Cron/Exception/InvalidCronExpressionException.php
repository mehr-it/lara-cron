<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 29.03.19
	 * Time: 10:36
	 */

	namespace MehrIt\LaraCron\Cron\Exception;


	use Throwable;

	class InvalidCronExpressionException extends \InvalidArgumentException
	{
		protected $expression;

		/**
		 * Construct the exception. Note: The message is NOT binary safe.
		 * @link https://php.net/manual/en/exception.construct.php
		 * @param string $expression The field expression
		 * @param string $message [optional] The Exception message to throw.
		 * @param int $code [optional] The Exception code.
		 * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
		 * @since 5.1.0
		 */
		public function __construct(string $expression, string $message = "", int $code = 0, Throwable $previous = null) {

			$this->expression = $expression;

			if (!$message)
				$message = "Expression \"$expression\" is not a valid cron expression";

			parent::__construct($message, $code, $previous);
		}

		/**
		 * Gets the expression
		 * @return string The expression
		 */
		public function getExpression(): string {
			return $this->expression;
		}
	}