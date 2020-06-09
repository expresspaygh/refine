<?php

namespace Expay\Refine\Rules;

class Boolean extends Rule
{
  /**
   * Process a boolean value
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value) {
    if (in_array($value, [true, 1, "TRUE", "true"], true))
      return true;
    else if (in_array($value, [false, 0, "FALSE", "false"], true))
      return false;
  }
}
