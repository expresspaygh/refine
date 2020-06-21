<?php

namespace Expay\Refine\Rules;

/**
 * Nullable
 */
class Nullable extends Rule
{
  /**
   * apply: Remove any nasty characters from the supplied string
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return string
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    return $value;
  }
}
