<?php
namespace Limonte\Tests;

use Limonte\AdblockRule;

class AdblockRuleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRegex()
    {
        $rule = new AdblockRule('/slashes should be trimmed/');
        $this->assertEquals('slashes should be trimmed', $rule->getRegex());
    }

    /**
     * @expectedException \Limonte\InvalidRuleException
     */
    public function testInvalidRegex()
    {
        $invalidRule = new AdblockRule('//');
        $invalidRule->getRegex();
    }

    public function testEscapeSpecialCharacters()
    {
        $rule = new AdblockRule('.$+?{}()[]/\\');
        $this->assertEquals('\.\$\+\?\{\}\(\)\[\]\/\\\\', $rule->getRegex());
    }

    public function testCaret()
    {
        $rule = new AdblockRule('domain^');
        $this->assertEquals('domain([^\w\d_\-\.%]|$)', $rule->getRegex());
    }

    public function testAsterisk()
    {
        $rule = new AdblockRule('domain*');
        $this->assertEquals('domain.*', $rule->getRegex());
    }

    public function testVerticalBars()
    {
        $rule = new AdblockRule('||domain');
        $this->assertEquals('^([^:\/?#]+:)?(\/\/([^\/?#]*\.)?)?domain', $rule->getRegex());

        $rule = new AdblockRule('|domain');
        $this->assertEquals('^domain', $rule->getRegex());

        $rule = new AdblockRule('domain|bl||ah');
        $this->assertEquals('domain\|bl\|\|ah', $rule->getRegex());
    }

    public function testMatchUrl()
    {
        $rule = new AdblockRule('swf|');
        $this->assertTrue($rule->matchUrl("http://example.com/annoyingflash.swf"));
        $this->assertFalse($rule->matchUrl("http://example.com/swf/index.html"));
    }

    public function testComment()
    {
        $rule = new AdblockRule('!this is comment');
        $this->assertTrue($rule->isComment());
        $rule = new AdblockRule('[Adblock Plus 1.1]');
        $this->assertTrue($rule->isComment());
        $rule = new AdblockRule('non-comment rule');
        $this->assertFalse($rule->isComment());
    }
}
