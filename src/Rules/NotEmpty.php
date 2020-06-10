<?php

namespace Expay\Refine\Rules;
use Expay\Refine\Exceptions\InvalidField;

class NotEmpty extends Rule
{
  /**
   * Throw an error if the field is empty
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value, $key, $request) {
    if (!array_key_exists($key, $request))
      throw new InvalidField("Field '$key' should not be empty");}
}
