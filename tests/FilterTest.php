<?php

use Expay\Refine\Filter;
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
    $flt = new Filter(["string_field" => "string"], ["string_field" => "asdf"]);
    $this->assertEquals([
      "status" => 0,
      "message" => "Success",
      "output" => ["string_field" => "asdf"]
    ], $flt->getFilterResponse());
  }

  /**
   * Check that booleans are handled in the expressPay way (string of TRUE and
   * FALSE)
   *
   * @dataProvider booleanHandlingProvider
   */
  public function testBooleanHandling($input, $output)
  {
    $rslt = Filter::check($this->response(["some_bool" => "bool"]), $input);
    $this->assertEquals($output, $rslt["output"]);
  }

  public function booleanHandlingProvider()
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
    $rslt = Filter::check(["field" => "404-not-found"], ["field" => "asdf"]);
    $this->assertEmpty($rslt);
  }

  /**
   * Checks what happens when we don't find a php filter for something
   */
  public function testFilterNotProvided()
  {
    $rslt = Filter::check(["field" => "404-not-found"], []);
    $this->assertEmpty($rslt);
  }

  /**
   * Ensure that nullify fields are handled correctly
   */
  public function testNullifyHandling()
  {
    $rslt = Filter::check(["field" => "null"], ["field" => "asdf"]);
    $this->assertEmpty($rslt);
  }

  /**
   * Test the behaviour that happens when a filer option is not provided for a
   * request key
   */
  public function testDefaultFilterOptions()
  {
    $rslt = Filter::check([], ["string" => "<a>asdf</a>"]);
    $this->assertEquals($this->response(["string" => "asdf"]), $rslt);
  }

  /**
   * Test the string sanitization behaviours
   */
  public function testStringFilterOptions()
  {
    $rslt = Filter::check(["field" => "string"], ["field" => "<a>asdf</a>"]);
    $this->assertEquals($this->response(["field" => "asdf"]), $rslt);
  }

  /**
   * Ensure that arrays behave appropriately
   */
  public function testArrayFilterOptions()
  {
    $rslt = Filter::check(["field" => "array"], ["field" => [1, 2, 3]]);
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
    $rslt = Filter::check(["field" => "array"], $_REQUEST);
    $this->assertEquals($this->response(["field" => [1, 2, 3]]), $rslt);
  }
}
