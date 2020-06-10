<?php

namespace Expay\Refine\Rules;

class PHPFilter extends Rule
{
  protected $definition;

  /**
   * @param array $definition
   */
  public function __construct(array $definition)
  {
    $this->definition = $definition;
  }

  /**
   * Process a boolean value
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value, string $key, array $request)
  {
    return filter_var_array(["field" => $value], ["field" => $this->definition])["field"];
  }
}
