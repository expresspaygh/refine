<?php

namespace Expay\Refine\Rules;

/**
 * Perform some preprocessing on a request value before it's filtered
 */
abstract class Rule
{
  /**
   * Apply the rule to the given key and value and return the result
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value, string $key, array $request) {
    return $value;
  }
  
  /**
   * applyClass
   *
   * @param  mixed $value
   * @param  mixed $classObj
   * @param  mixed $method
   * @return void
   */
  public function applyClass($value,$classObj,$method)
  {
    return $value;
  }
}
