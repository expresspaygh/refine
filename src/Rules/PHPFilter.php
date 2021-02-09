<?php

namespace Expay\Refine\Rules;

/**
 * PHPFilter
 */
class PHPFilter extends Rule
{  
  /**
   * definition
   *
   * @var mixed
   */
  protected $definition;

  /**
   * __construct
   *
   * @param  mixed $definition
   * @return void
   */
  public function __construct($definition)
  {
    $this->definition = $definition;
  }

  /**
   * apply: Process filter rules
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return void
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    return filter_var_array(["field" => $value], ["field" => $this->definition])["field"];
  }
}
