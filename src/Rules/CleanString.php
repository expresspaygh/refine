<?php

namespace Expay\Refine\Rules;

class CleanString
{
  /**
   * Remove any nasty characters from the supplied string
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply(string $value): string {
    return preg_replace("/[^A-Za-z0-9-_., ]/", "", $value);
  }
}
