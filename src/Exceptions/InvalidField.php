<?php

namespace Expay\Refine\Exceptions;

class InvalidField extends \Exception {
  public $data;

  public function __construct(
    $message,
    $code = null,
    \Exception $previous = null
  ) {
    parent::__construct($message, $code, $previous);
  }
}
