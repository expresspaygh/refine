<?php

namespace Expay\Refine\Rules;

/**
 * Rule: Perform some preprocessing on a request value before it's filtered
 */
abstract class Rule
{
  /**
   * apply: Apply the rule to the given key and value and return the result
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return void
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    return $value;
  }
}
