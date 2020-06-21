<?php

namespace Expay\Refine\Exceptions;

/**
 * ValidationError
 */
class ValidationError extends \Exception {  
  /**
   * data
   *
   * @var mixed
   */
  public $data;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct(
    $message,
    $code = null,
    \Exception $previous = null
  ) {
    parent::__construct($message, $code, $previous);
  }
}
