<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Expay\Refine\Rules\Validate;
use Expay\Refine\Rules\CleanTags;
use Expay\Refine\Rules\CleanString;

/**
 * FilterRulesTest
 */
class FilterRulesTest extends TestCase
{    
  /**
   * validate
   *
   * @var mixed
   */
  private $validate;
  /**
   * cleanTags
   *
   * @var mixed
   */
  private $cleanTags;  
  /**
   * cleanString
   *
   * @var mixed
   */
  private $cleanString;  
  /**
   * faker
   *
   * @var mixed
   */
  private $faker;
    
  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    $this->validate=new Validate;
    $this->cleanTags=new CleanTags;
    $this->faker=Factory::create();
    $this->cleanString=new CleanString;
  }
  
  /**
   * testCleanString
   *
   * @return void
   */
  public function testCleanString()
  {
    $string=$this->faker->word;
    $this->assertEquals($string, $this->cleanString->apply("<a>$string</a>", "", []));
  }
  
  /**
   * testStripTags
   *
   * @return void
   */
  public function testStripTags()
  {
    $string=$this->faker->word;
    $this->assertEquals($string, $this->cleanTags->apply("<a>$string</a>", "", []));
  }
  
  /**
   * testValidator
   *
   * @return void
   */
  public function testValidator()
  {
    $result=$this->validate->apply($this->faker->email,'email',[],['email'=>'required|array']);
    $this->assertNull($result);
  }
}
