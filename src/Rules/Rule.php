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
  public function apply($value) {
    return $value;
  }
}
