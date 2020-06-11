<?php

namespace Expay\Refine\Rules;

class ValidationClass extends Rule
{
  protected $definition;

  /**
   * @param array $definition
   */
  public function __construct(array $definition)
  {
    $this->definition = $definition;
  }

  /**
   * applyClass
   *
   * @param  mixed $value
   * @param  mixed $classobj
   * @param  mixed $method
   * @return void
   */
  public function applyClass($value,$classobj,$method)
  {
    error_log(print_r($value,true));
    error_log(print_r($classobj,true));
    error_log(print_r($method,true));
    exit;
    return $value;
  }
}
