<?php
use Expay\Refine\Rules;
use PHPUnit\Framework\TestCase;

class FilterRulesTest extends TestCase
{
  public function testCleanString()
  {
    $this->assertEquals("aasdfa", (new Rules\CleanString())->apply("<a>asdf</a>", "", []));
  }

  public function testStripTags()
  {
    $this->assertEquals("asdf", (new Rules\CleanTags())->apply("<a>asdf</a>", "", []));
  }
}
