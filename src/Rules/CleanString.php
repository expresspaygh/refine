<?php

namespace Expay\Refine\Rules;

class CleanString extends Rule
{
  /**
   * Remove any nasty characters from the supplied string
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value, string $key, array $request): string {
    return preg_replace("/[^A-Za-z0-9-_., ]/", "", $value);
  }
}
