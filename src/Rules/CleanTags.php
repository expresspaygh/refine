<?php

namespace Expay\Refine\Rules;

class CleanTags extends Rule
{
  /**
   * apply: Remove html tags from the given string
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return string
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[]): string
  {
    return strip_tags($value);
  }
}
