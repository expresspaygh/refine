<?php

namespace Expay\Refine;

/**
 * Rules
 * A class to store functions we might want to run on incoming data before
 * performing filtering.
 */
class Rules
{
    
  /**
   * check
   *
   * @param  mixed $func
   * @return bool
   */
  public static function check(string $func = null): bool
  {
    return (!is_null($func)) ? method_exists(__CLASS__, $func) : false;
  }
  
  /**
   * clean_string
   *
   * @param  mixed $string
   * @return string
   */
  public static function clean_string(string $string): string
  {
    return preg_replace("/[^A-Za-z0-9-_., ]/", "", $string);
  }
  
  /**
   * clean_tags
   *
   * @param  mixed $string
   * @return string
   */
  public static function clean_tags(string $string): string
  {
    return strip_tags($string);
  }
}
