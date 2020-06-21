<?php

namespace Expay\Refine\Tests\Providers;

use Rakit\Validation\Rule as ValidationRule;

/**
 * ValidationRuleObjectProvider
 */
class ValidationRuleObjectProvider extends ValidationRule
{    
  /**
   * message
   *
   * @var string
   */
  protected $message = "";  
  /**
   * implicit
   *
   * @var bool
   */
  protected $implicit = true;
    
  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $this->message=":value is not a valid object";
  }
    
  /**
   * check
   *
   * @param  mixed $value
   * @return bool
   */
  public function check($value) : bool
  {
    return is_object($value);
  }
}