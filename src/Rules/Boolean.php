<?php

namespace Expay\Refine\Rules;

class Boolean extends Rule
{
  protected $stringOutput;

  /**
   * @param ?string $stringOutput "upper" | "lower"
   */
  public function __construct($stringOutput = null) {
    $this->stringOutput = $stringOutput;
  }

  /**
   * apply: Process a boolean value
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return void
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[]) {
    if (in_array($value, [true, 1, "TRUE", "true"], true))
      $value = true;
    else if (in_array($value, [false, 0, "FALSE", "false"], true))
      $value = false;
    else return null;

    if ($this->stringOutput === "upper")
      return $value ? "TRUE" : "FALSE";
    else if ($this->stringOutput === "lower")
      return $value ? "true" : "false";
    else
      return $value;
  }
}
