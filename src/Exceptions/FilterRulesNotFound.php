<?php

namespace Expay\Refine\Exceptions;

class FilterRulesNotFound extends \Exception {
  public $data;

  public function __construct(
    $type,
    $code = null,
    \Exception $previous = null
  ) {
    parent::__construct("Rules not found for field type: '$type'", $code, $previous);
  }
}
