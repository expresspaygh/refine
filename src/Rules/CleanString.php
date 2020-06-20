<?php

namespace Expay\Refine\Rules;

class CleanString extends Rule
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
  public function apply($value, string $key="", array $request=[], array $validationRules=[]) : string
  {
    return preg_replace("/[^A-Za-z0-9-_., ]/", "", $value);
  }
}
