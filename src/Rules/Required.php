<?php

namespace Expay\Refine\Rules;
use Expay\Refine\Exceptions\InvalidField;

class Required extends Rule
{
  /**
   * Throw an error if the field is absent
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value, $key, $request) {
    if (!array_key_exists($key, $request))
      throw new InvalidField("Field '$key' is required");

    return $value;
  }
}
