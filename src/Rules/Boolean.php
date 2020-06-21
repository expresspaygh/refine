<?php

namespace Expay\Refine\Rules;

class Boolean extends Rule
{  
  /**
   * stringOutput
   *
   * @var mixed
   */
  protected $stringOutput;  
  /**
   * innerValue
   *
   * @var mixed
   */
  private $innerValue;

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
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    if (in_array($value, [true, 1, "TRUE", "true"], true))
    {
      $this->innerValue = true;
    }
    elseif (in_array($value, [false, 0, "FALSE", "false"], true))
    {
      $this->innerValue = false;
    }
    else
    {
      return null;
    }

    if ($this->stringOutput === "upper")
    {
      return $this->innerValue ? "TRUE" : "FALSE";
    }
    elseif ($this->stringOutput === "lower")
    {
      return $this->innerValue ? "true" : "false";
    }

    return $value;
  }
}
