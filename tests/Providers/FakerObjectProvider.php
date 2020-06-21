<?php

namespace Expay\Refine\Tests\Providers;

use Faker\Provider\Base as FakerBase;

/**
 * FakerObjectProvider
 */
class FakerObjectProvider extends FakerBase
{  
  /**
   * getStringArray
   *
   * @param  mixed $count
   * @return array
   */
  public function getStringObject(int $count=3): \stdClass
  {
    $retData=new \stdClass();

    for($i=0; $i < $count; $i++)
    {
      $retData->{$this->generator->word}=$this->generator->word;
    }

    return $retData;
  }
}