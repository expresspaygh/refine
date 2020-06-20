<?php

namespace Expay\Refine\Rules;

use Rakit\Validation\Validator;
use Expay\Refine\Exceptions\ValidationError;

/**
 * Validate
 */
class Validate extends Rule
{  
  /**
   * validator
   *
   * @var mixed
   */
  private $validator;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $this->validator=new Validator;
  }

  /**
   * apply
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return void
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    $validation = $this->validator->validate([$key=>$value], $validationRules);
    $errors = $validation->errors();

    if(!empty($errors->first($key)))
    {
      throw new ValidationError($errors->first($key));
    }

    return $value;
  }
}
