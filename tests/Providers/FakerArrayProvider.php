<?php

namespace Expay\Refine\Tests\Providers;

use Faker\Provider\Base as FakerBase;

/**
 * FakerArrayProvider
 */
class FakerArrayProvider extends FakerBase
{  
  /**
   * getStringArray
   *
   * @param  mixed $count
   * @return array
   */
  public function getStringArray(int $count=3):array
  {
    $retData=array();

    for($i=0; $i < $count; $i++)
    {
      array_push($retData,$this->generator->word);
    }

    return $retData;
  }
}