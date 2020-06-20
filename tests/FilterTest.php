<?php

use Expay\Refine\Filter;
use Expay\Refine\Rules;
use PHPUnit\Framework\TestCase;

/**
 * FilterTest
 */
class FilterTest extends TestCase
{
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
    $resp = (new Filter)
      ->addField("string_field", "string")
      ->check(["string_field" => "asdf"]);

    $this->assertEquals([
      "status" => 0,
      "message" => "Success",
      "output" => ["string_field" => "asdf"]
    ], $resp);
  }

  /**
   * testBooleanHandling: Check that booleans are handled in the expressPay way (string of TRUE and
   * FALSE)
   *
   * @param  mixed $input
   * @param  mixed $output
   * @source $this->booleanHandlingProvider
   * @return void
   */
  public function testBooleanHandling()
  {
    $input=["bool_field"=>"true"];
    $output=["bool_field"=>"TRUE"];
    $rslt = (new Filter)
          ->addField("bool_field", "bool")
          ->replaceRules("bool", [new Rules\Boolean("lower")])
          ->check($input);
    $this->assertEquals($this->response($output), $rslt);
  }
  
  /**
   * booleanHandlingProvider
   *
   * @return void
   */
  public function booleanHandlingProvider()
  {
    return [
      [["bool_field" => "TRUE"], ["bool_field" => true]],
      [["bool_field" => "FALSE"], ["bool_field" => false]],
      [["bool_field" => 'true'], ["bool_field" => true]],
      [["bool_field" => 'false'], ["bool_field" => false]],
      [["bool_field" => true], ["bool_field" => true]],
      [["bool_field" => false], ["bool_field" => false]],
      [["bool_field" => 1], ["bool_field" => true]],
      [["bool_field" => 0], ["bool_field" => false]],
    ];
  }

  /**
   * testStringBooleanHandling: Check that booleans are handled in the expressPay way (string of TRUE and
   * FALSE)
   *
   * @param  mixed $input
   * @param  mixed $output
   * @source $this->stringBooleanHandlingProvider
   * @return void
   */
  public function testStringBooleanHandling()
  {
    $input=["bool_field"=>"true"];
    $output=["bool_field"=>"TRUE"];
    $rslt = (new Filter)
          ->addField("bool_field", "bool")
          ->replaceRules("bool", [new Rules\Boolean("lower")])
          ->check($input);
    $this->assertEquals($this->response($output), $rslt);
  }
  
  /**
   * stringBooleanHandlingProvider
   *
   * @return void
   */
  public function stringBooleanHandlingProvider()
  {
    return [
      [["bool_field" => "TRUE"], ["bool_field" => "TRUE"]],
      [["bool_field" => "FALSE"], ["bool_field" => "FALSE"]],
      [["bool_field" => 'true'], ["bool_field" => "TRUE"]],
      [["bool_field" => 'false'], ["bool_field" => "FALSE"]],
      [["bool_field" => true], ["bool_field" => "TRUE"]],
      [["bool_field" => false], ["bool_field" => "FALSE"]],
      [["bool_field" => 1], ["bool_field" => "TRUE"]],
      [["bool_field" => 0], ["bool_field" => "FALSE"]],
    ];
  }

  /**
   * testFilterNotFoundError: Ensure that we throw an error when the user provides a filter that was not
   * found
   *
   * @return void
   */
  public function testFilterNotFoundError()
  {
    $rslt = (new Filter)
          ->addField("field", "asdf")
          ->check(["field" => "404-not-found"]);
    $this->assertEquals($this->response([]), $rslt);
  }

  /**
   * testFilterNotProvided: Checks what happens when we don't find a php filter for something
   *
   * @return void
   */
  public function testFilterNotProvided()
  {
    $rslt = (new Filter)->check(["field" => "404-not-found"]);
    $this->assertEquals($this->response([]), $rslt);
  }

  /**
   * testNullifyHandling: Ensure that nullify fields are handled correctly
   *
   * @return void
   */
  public function testNullifyHandling()
  {
    $rslt = (new Filter)
          ->addField("field", "null")
          ->check(["field" => "asdf"]);
    $this->assertEquals($this->response([]), $rslt);
  }

  /**
   * testDefaultFilterOptions: Test the behaviour that happens when a filer option is not provided for a
   * request key
   *
   * @return void
   */
  public function testDefaultFilterOptions()
  {
    $rslt = (new Filter)->check(["string" => "<a>asdf</a>"]);
    $this->assertEquals($this->response(["string" => "asdf"]), $rslt);
  }

  /**
   * testStringFilterOptions: Test the string sanitization behaviours
   *
   * @return void
   */
  public function testStringFilterOptions()
  {
    $rslt = (new Filter)
          ->addField("field", "string")
          ->check(["field" => "<a>asdf</a>"]);
    $this->assertEquals($this->response(["field" => "asdf"]), $rslt);
  }

  /**
   * testArrayFilterOptions: Ensure that arrays behave appropriately
   *
   * @return void
   */
  public function testArrayFilterOptions()
  {
    $rslt = (new Filter)
          ->addField("field", "array")
          ->check(["field" =>[1, 2, 3]]);
    $this->assertEquals($this->response(["field" => [1, 2, 3]]), $rslt);
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
    $_REQUEST = ["field" => [1, 2, 3]];
    $rslt = (new Filter)
          ->addField("field", "array")
          ->check();
    $this->assertEquals($this->response(["field" => [1, 2, 3]]), $rslt);
  }
  
  /**
   * testEmailFilter
   *
   * @return void
   */
  public function testEmailFilter()
  {
    $rslt = (new Filter)->check(["email" => "test@gmail.com"]);
    $this->assertEquals($this->response(["email" => "test@gmail.com"]), $rslt);
  }
  
  /**
   * testIntFilter
   *
   * @return void
   */
  public function testIntFilter()
  {
    $rslt = (new Filter)->check(["int" => 1]);
    $this->assertEquals($this->response(["int" =>  1]), $rslt);
  }
  
  /**
   * testRequired
   *
   * @return void
   */
  public function testRequired()
  {
    $rslt = (new Filter)->addField("field", [new Rules\Required])->check([]);
    $this->assertEquals([
      'status' => 2,
      'message' => 'Bad Request, kindly check and try again',
      'output' => ['field' => "Field 'field' is required"]
    ], $rslt);
  }
}
