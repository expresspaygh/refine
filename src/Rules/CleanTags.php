<?php

namespace Expay\Refine\Rules;

class CleanTags
{
  /**
   * Remove html tags from the given string
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply(string $value): string {
    return strip_tags($value);
  }
}
