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
   * vRules
   *
   * @var array
   */
  private $vRules=array();  
  /**
   * customRules
   *
   * @var array
   */
  private $customRules=array();
  /**
   * throwException
   *
   * @var bool
   */
  private $throwException=true;
  
  /**
   * __construct
   *
   * @return void
   */
  public function __construct(array $vRules=[], array $customRules=[])
  {
    $this->vRules=$vRules;
    $this->customRules=$customRules;
  }

  /**
   * apply
   * 
   * @package https://github.com/rakit/validation
   *
   * @param  mixed $value
   * @param  mixed $key
   * @param  mixed $request
   * @param  mixed $validationRules
   * @return void
   */
  public function apply($value, string $key="", array $request=[], array $validationRules=[])
  {
    // check if in func validation rules was passed
    if(!empty($validationRules) && empty($this->vRules))
    {
      $this->throwException=false;
      $this->vRules=$validationRules;
    }
    // check if both internal and external rules are empty
    elseif(empty($validationRules) && empty($this->vRules))
    {
      throw new ValidationError("No validation rules found");
    }

    // init validation
    $validator=new Validator;

    // apply custom rules if any
    if(!empty($this->customRules))
    {
      foreach($this->customRules as $rkey=>$rvalue)
      {
        if(is_object($rvalue))
        {
          $validator->addValidator($rkey, $rvalue);
        }else{
          throw new ValidationError("Custom rule($rkey) is not an object - ".gettype($cvRule));
        }
      }
    }

    // run validation
    $validation = $validator->validate([$key=>$value], $this->vRules);
    $errors = $validation->errors();

    // check for errors
    if(!empty($errors->first($key)))
    {
      if(!$this->throwException)
        return $errors->toArray();
      else
        throw new ValidationError($errors->first($key));
    }

    // respond
    return $value;
  }
}
