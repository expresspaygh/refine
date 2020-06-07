<?php

require_once(__DIR__."/../vendor/autoload.php");

use Expay\Refine\Filter;

/**
 * FilterCases
 */
trait FilterCases
{
  /**
   * test_check
   *
   * @return void
   */
  private function filter_check()
  {
    $this->request=[
      "some_url" => "https://foobar.com",
      "some_email" => "someone@expresspaygh.com",
      "some_int" => 1,
      "some_bool" => true,
      "some_array" => [1, 2, 3],
      "some_ip" => "127.0.0.1",
      "some_string" => "<a href='/asdf'>foobar</a>"
    ];

    $this->options=[
      'some_url' => 'url',
      'some_email' => 'email',
      'some_int' => 'int',
      "some_bool" => "bool",
      "some_array" => "array",
      "some_ip" => "ip",
      "some_string" => "clean_string"
    ];

    return Filter::check($this->options,$this->request);
  }
}