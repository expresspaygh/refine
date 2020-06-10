<?php

namespace Expay\Refine\Rules;

class CleanTags extends Rule
{
  /**
   * Remove html tags from the given string
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value, string $key, array $request): string {
    return strip_tags($value);
  }
}
