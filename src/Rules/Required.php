<?php

namespace Expay\Refine\Rules;
use Expay\Refine\Exceptions\InvalidField;

/**
 * Required
 */
class Required extends Rule
{
  /**
   * apply: Throw an error if the field is absent
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return void
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    if (!array_key_exists($key, $request))
      throw new InvalidField("Field '$key' is required");
    return $value;
  }
}
