<?php

require_once(__DIR__."/../vendor/autoload.php");

use Expay\Refine\Filter;

class CVV extends Rule
{
	public function apply($value, string $key, array $request): string
	{
		if (preg_match("/^\d\d\d$/", $value))
			return $value;
		throw new InvalidField("Invalid cvv");
	}
}

/**
 * FilterCases
 */
trait FilterCases
{
  /**
   * test_check
   *
   * @return void
   */
  private function filter_check()
  {
    $result = (new Filter())
    ->addRule("cvv", new CVV)
    ->check(["cvv" => "123"]);

    var_dump($result);
  }
}