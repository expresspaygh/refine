<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Expay\Refine\Rules\Validate;
use Expay\Refine\Rules\CleanTags;
use Expay\Refine\Rules\CleanString;
use Expay\Refine\Tests\Providers\FakerObjectProvider;
use Expay\Refine\Tests\Providers\ValidationRuleObjectProvider;

/**
 * FilterRulesTest
 */
class FilterRulesTest extends TestCase
{
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
    parent::__construct();

    $this->faker=Factory::create();
    $this->cleanTags=new CleanTags;
    $this->cleanString=new CleanString;

    $this->faker->addProvider(new FakerObjectProvider($this->faker));
  }
  
  /**
   * testCleanString
   *
   * @return void
   */
  public function testCleanString()
  {
    $string=$this->faker->word;
    $result=$this->cleanString->apply("//$string//"); 
    $this->assertEquals($string, $result);
  }
  
  /**
   * testStripTags
   *
   * @return void
   */
  public function testStripTags()
  {
    $string=$this->faker->word;
    $this->assertEquals($string, $this->cleanTags->apply("<a>$string</a>"));
  }
  
  /**
   * testValidatorWithoutCustomRules
   *
   * @return void
   */
  public function testValidatorWithoutCustomRules()
  {
    $email=$this->faker->email;
    $validationRules=['email'=>'required|email'];

    $validate=new Validate();
    $result=$validate->apply($email,'email',[],$validationRules);
    $this->assertIsString($result);
  }

  /**
   * testValidatorWithCustomRules
   *
   * @return void
   */
  public function testValidatorWithCustomRules()
  {
    $randomObj=$this->faker->getStringObject();
    $validationRules=['randomObj'=>'required|object_value'];
    $customRules=["object_value"=>new ValidationRuleObjectProvider];

    $validate=new Validate([],$customRules);
    $result=$validate->apply($randomObj,'randomObj',[],$validationRules);

    $this->assertIsObject($result);
  }
}
