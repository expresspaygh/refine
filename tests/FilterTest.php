<?php

use Faker\Factory;
use Expay\Refine\Rules;
use Expay\Refine\Filter;
use PHPUnit\Framework\TestCase;
use Expay\Refine\Exceptions\ValidationError;
use Expay\Refine\Tests\Providers\FakerArrayProvider;
use Expay\Refine\Tests\Providers\FakerObjectProvider;
use Expay\Refine\Tests\Providers\ValidationRuleObjectProvider;

/**
 * FilterTest
 */
class FilterTest extends TestCase
{
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

    // load custom providers
    $this->faker->addProvider(new FakerArrayProvider($this->faker));
    $this->faker->addProvider(new FakerObjectProvider($this->faker));
  }
  
  /**
   * response: Wrap the given data in a success response for ease of testing
   *
   * @param  mixed $output
   * @return array
   */
  protected function response(array $output): array
  {
    return [
      "status" => 0,
      "message" => "Success",
      "output" => $output
    ];
  }

  /**
   * testBasic: Basic test to make sure all's good
   *
   * @return void
   */
  public function testBasic()
  {
    $filter=new Filter;

    $fields=["string_field"=>"string"];
    $data=["string_field" => $this->faker->word];

    $this->assertEquals(
      $this->response($data), 
      $filter->addFields($fields)->check($data)
    );
  }

  /**
   * testBooleanHandling: Check that booleans are not string
   *
   * @param  mixed $input
   * @param  mixed $output
   * @return void
   */
  public function testBooleanHandling()
  {
    $filter=new Filter;

    $data=[
      "true_field"=>true,
      "false_field"=>false
    ];

    $fields=[
      "true_field"=>"bool",
      "false_field"=>"bool"
    ];

    $this->assertEquals(
      $this->response($data), 
      $filter->addFields($fields)->check($data)
    );
  }

  /**
   * testStringBooleanHandling: Check booleans (string of TRUE and FALSE)
   *
   * @param  mixed $input
   * @param  mixed $output
   * @return void
   */
  public function testStringBooleanHandling()
  {
    $filter=new Filter;

    $data=[
      "true_field_lower"=>"true",
      "false_field_lower"=>"false",
      "true_field_upper"=>"TRUE",
      "false_field_upper"=>"FALSE"
    ];

    $fields=[
      "true_field_lower"=>"bool",
      "false_field_lower"=>"bool",
      "true_field_upper"=>"bool",
      "false_field_upper"=>"bool"
    ];

    $lowerBoolReturnType=[new Rules\Boolean("lower")];
    $upperBoolReturnType=[new Rules\Boolean("upper")];

    $rslt = $filter->addFields($fields)
          ->replaceRules("true_field_lower", $lowerBoolReturnType)
          ->replaceRules("false_field_lower", $lowerBoolReturnType)
          ->replaceRules("true_field_upper", $upperBoolReturnType)
          ->replaceRules("false_field_upper", $upperBoolReturnType)
          ->check($data);

    $this->assertEquals($this->response($data), $rslt);
  }

  /**
   * testNullifyHandling: Ensure that nullify fields are handled correctly
   *
   * @return void
   */
  public function testNullifyHandling()
  {
    $filter=new Filter;

    $fields=["field"=>[new Rules\Nullable]];
    $data=["field"=>"*".$this->faker->word,"*"];

    $this->assertEquals(
      $this->response($data), 
      $filter->addFields($fields)->check($data)
    );
  }

  /**
   * testDefaultFilterOptions: Test the behaviour that happens when a filer option is not provided for a
   * request key
   *
   * @return void
   */
  public function testDefaultFilterOptions()
  {
    $filter=new Filter;
    $string=$this->faker->word;

    $fields=["field"=>$string];
    $data=["field"=>"<a>".$string."</a>"];
    
    $this->assertEquals(
      $this->response($fields), 
      $filter->check($data)
    );
  }

  /**
   * testStringFilterOptions: Test the string sanitization behaviours
   *
   * @return void
   */
  public function testStringFilterOptions()
  {
    $filter=new Filter;
    $string=$this->faker->word;

    $fields=["field"=>"string"];
    $data=["field"=>"<a>".$string."</a>"];
    $assert=["field"=>$string];
    
    $this->assertEquals(
      $this->response($assert), 
      $filter->addFields($fields)->check($data)
    );
  }

  /**
   * testArrayFilterOptions: Ensure that arrays behave appropriately
   *
   * @return void
   */
  public function testArrayFilterOptions()
  {
    $filter=new Filter;

    $fields=["field"=>"array"];
    $data=["field"=>$this->faker->getStringArray()];

    $this->assertEquals(
      $this->response($data), 
      $filter->addFields($fields)->check($data)
    );
  }

  /**
   * testDefaultRequest: Ensure that the request is fetched from the php variable if it's not
   * provided
   *
   * @return void
   */
  public function testDefaultRequest()
  {
    global $_REQUEST;
    $filter=new Filter;

    $fields=["field"=>"array"];
    $_REQUEST = ["field" => $this->faker->getStringArray()];

    $this->assertEquals(
      $this->response($_REQUEST),
      $filter->addFields($fields)->check()
    );
  }
  
  /**
   * testEmailFilter
   *
   * @return void
   */
  public function testEmailFilter()
  {
    $filter=new Filter;
    $data=["email" => $this->faker->email];

    $this->assertEquals(
      $this->response($data), 
      $filter->check($data)
    );
  }
  
  /**
   * testIntFilter
   *
   * @return void
   */
  public function testIntFilter()
  {
    $filter=new Filter;
    $data=["int" => 1];

    $this->assertEquals(
      $this->response($data), 
      $filter->check($data)
    );
  }
  
  /**
   * testRequired
   *
   * @return void
   */
  public function testRequired()
  {
    $filter=new Filter;
    $fields=["field"=>[new Rules\Required]];

    $rslt = $filter->addFields($fields)->check([]);
    $response=[
      'status' => 2,
      'message' => 'Bad Request, kindly check and try again',
      'output' => ['field' => "Field 'field' is required"]
    ];

    $this->assertEquals($response, $rslt);
  }

  /**
   * testValidationFilterSuccess
   *
   * @return void
   */
  public function testValidationFilterSuccess()
  {
    try
    {
      $filter=new Filter;

      $vRules=["email"=>"required|present|email"];
      $fields=["email"=>[new Rules\Validate($vRules),new Rules\CleanTags]];
      $data=["email"=>$this->faker->email];

      $result=$filter->addFields($fields)->check($data);
    }
    catch(ValidationError $e)
    {
      $result=$e->getMessage();
    }
    
    $this->assertEquals($this->response($data), $result);
  }

  /**
   * testValidationFilterException
   *
   * @return void
   */
  public function testValidationFilterException()
  {
    try
    {
      $filter=new Filter;

      $vRules=["email"=>"required|present|email|in:jefferyosei@expresspaygh.com,ellisadigvom@expresspaygh.com"];
      $fields=["email"=>[new Rules\Validate($vRules),new Rules\CleanTags]];
      $data=["email"=>$this->faker->email];

      $result=$filter->addFields($fields)->check($data);
    }
    catch(ValidationError $e)
    {
      $result=$e;
    }

    $this->assertIsObject($result);
    $this->assertEquals(
      $result->getMessage(),
      "The Email only allows 'jefferyosei@expresspaygh.com', or 'ellisadigvom@expresspaygh.com'"
    );
  }
  
  /**
   * testValidationCustomRules
   *
   * @return void
   */
  public function testValidationCustomRules()
  {
    try
    {
      $filter=new Filter;

      $vRules=['randomObj'=>'required|object_value'];
      $customRules=["object_value"=>new ValidationRuleObjectProvider];

      $fields=["obj_field"=>[new Rules\Validate($vRules,$customRules)]];
      $objData=new \stdClass();
      $objData->{"hello"}="hello sir";
      $data=["obj_field"=>$objData];

      $result=$filter->addFields($fields)->check($data);
    }
    catch(ValidationError $e)
    {
      $result=$e->getMessage();
    }
    
    $this->assertEquals($this->response($data), $result);
  }
}
