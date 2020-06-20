<?php

namespace Expay\Refine\Exceptions;

/**
 * InvalidField
 */
class InvalidField extends \Exception {  
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
