<?php

use Expay\Refine\Filter;
use Expay\Refine\Rules;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
  /**
   * Wrap the given data in a success response for ease of testing
   *
   * @param $output array
   */
  protected function response(array $output): array {
    return [
      "status" => 0,
      "message" => "Success",
      "output" => $output
    ];
  }

  /**
   * Basic test to make sure all's good
   */
  public function testSmoke()
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
   * Check that booleans are handled in the expressPay way (string of TRUE and
   * FALSE)
   *
   * @dataProvider booleanHandlingProvider
   */
  public function testBooleanHandling($input, $output)
  {
    $rslt = (new Filter)
          ->addField("bool_field", "bool")
          ->addRule("bool", new Rules\Boolean())
          ->check($input);
    $this->assertEquals($this->response($output), $rslt);
  }

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
   * Check that booleans are handled in the expressPay way (string of TRUE and
   * FALSE)
   *
   * @dataProvider stringBooleanHandlingProvider
   */
  public function testStringBooleanHandling($input, $output)
  {
    $rslt = (new Filter)
          ->addField("bool_field", "bool")
          ->addRule("bool", new Rules\Boolean("upper"))
          ->check($input);
    $this->assertEquals($this->response($output), $rslt);
  }

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
   * Ensure that we throw an error when the user provides a filter that was not
   * found
   */
  public function testFilterNotFoundError()
  {
    $rslt = (new Filter)
          ->addField("field", "asdf")
          ->check(["field" => "404-not-found"]);
    $this->assertEquals($this->response([]), $rslt);
  }

  /**
   * Checks what happens when we don't find a php filter for something
   */
  public function testFilterNotProvided()
  {
    $rslt = (new Filter)->check(["field" => "404-not-found"]);
    $this->assertEquals($this->response([]), $rslt);
  }

  /**
   * Ensure that nullify fields are handled correctly
   */
  public function testNullifyHandling()
  {
    $rslt = (new Filter)
          ->addField("field", "null")
          ->check(["field" => "asdf"]);
    $this->assertEquals($this->response([]), $rslt);
  }

  /**
   * Test the behaviour that happens when a filer option is not provided for a
   * request key
   */
  public function testDefaultFilterOptions()
  {
    $rslt = (new Filter)->check(["string" => "<a>asdf</a>"]);
    $this->assertEquals($this->response(["string" => "asdf"]), $rslt);
  }

  /**
   * Test the string sanitization behaviours
   */
  public function testStringFilterOptions()
  {
    $rslt = (new Filter)
          ->addField("field", "string")
          ->check(["field" => "<a>asdf</a>"]);
    $this->assertEquals($this->response(["field" => "asdf"]), $rslt);
  }

  /**
   * Ensure that arrays behave appropriately
   */
  public function testArrayFilterOptions()
  {
    $rslt = (new Filter)
          ->addField("field", "array")
          ->check(["field" =>[1, 2, 3]]);
    $this->assertEquals($this->response(["field" => [1, 2, 3]]), $rslt);
  }

  /**
   * Ensure that the request is fetched from the php variable if it's not
   * provided
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
}
