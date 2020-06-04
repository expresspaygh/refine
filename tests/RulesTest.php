<?php
use Expay\Refine\Rules;
use PHPUnit\Framework\TestCase;

class FilterRulesTest extends TestCase
{
  public function testCleanString()
  {
    $this->assertTrue(Rules::check("clean_string"));
    $this->assertEquals("aasdfa", Rules::clean_string("<a>asdf</a>"));
  }

  public function testStripTags()
  {
    $this->assertTrue(Rules::check("clean_tags"));
    $this->assertEquals("asdf", Rules::clean_tags("<a>asdf</a>"));
  }
}
