<?php

namespace Expay\Refine\Rules;

class PHPFilter extends Rule
{
  protected $filter;
  protected $flags;

  /**
   * @param array $definition
   */
  public function __construct(array $definition)
  {
    if (array_key_exists("filter", $definition))
      $this->filter = $definition["filter"];
    else
      $this->filter = FILTER_DEFAULT;

    $this->flags = [];
    if (array_key_exists("flags", $definition))
      $this->flags["flags"] = $definition["flags"];
    if (array_key_exists("options", $definition))
      $this->flags = array_merge($this->flags, $definition["options"]);
  }

  /**
   * Process a boolean value
   *
   * @param mixed $value the request value
   * @return mixed the processed value
   */
  public function apply($value)
  {
    return filter_var($value, $this->filter, $this->flags);
  }
}
