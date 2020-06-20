<?php

namespace Expay\Refine\Exceptions;

/**
 * ValidationError
 */
class ValidationError extends \Exception {
  /**
   * __construct
   *
   * @return void
   */
  public function __construct($message,$code=null,\Exception $previous=null)
  {
    parent::__construct($message, $code, $previous);
  }
}
