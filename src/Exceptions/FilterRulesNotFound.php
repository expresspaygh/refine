<?php

namespace Expay\Refine\Exceptions;

/**
 * FilterRulesNotFound
 */
class FilterRulesNotFound extends \Exception {  
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
    $type,
    $code = null,
    \Exception $previous = null
  ) {
    parent::__construct("Rules not found for field type: '$type'", $code, $previous);
  }
}
