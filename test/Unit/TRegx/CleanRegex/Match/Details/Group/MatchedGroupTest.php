<?php
namespace Test\Unit\TRegx\CleanRegex\Match\Details\Group;

use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Internal\Match\Details\Group\GroupDetails;
use TRegx\CleanRegex\Internal\Match\Details\Group\MatchedGroupOccurrence;
use TRegx\CleanRegex\Internal\MatchAllResults;
use TRegx\CleanRegex\Match\Details\Group\MatchedGroup;
use TRegx\CleanRegex\Match\Details\Group\MatchGroup;

class MatchedGroupTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetText()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $text = $matchGroup->text();

        // then
        $this->assertEquals('Nice matching', $text);
    }

    /**
     * @test
     */
    public function shouldMatch()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $matches = $matchGroup->matches();

        // then
        $this->assertTrue($matches);
    }

    /**
     * @test
     */
    public function shouldGetOffset()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $offset = $matchGroup->offset();

        // then
        $this->assertEquals(14, $offset);
    }

    /**
     * @test
     */
    public function shouldGetName()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $name = $matchGroup->name();

        // then
        $this->assertEquals('first', $name);
    }

    /**
     * @test
     */
    public function shouldGetIndex()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $index = $matchGroup->index();

        // then
        $this->assertEquals(1, $index);
    }

    /**
     * @test
     */
    public function shouldCastToString()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $text = (string)$matchGroup;

        // then
        $this->assertEquals('Nice matching', $text);
    }

    /**
     * @test
     */
    public function shouldControlMatched()
    {
        // given
        $matchGroup = $this->matchGroup();

        // when
        $orElse = $matchGroup->orElse('strtoupper');
        $orReturn = $matchGroup->orReturn(13);
        $orThrow = $matchGroup->orThrow();

        // then
        $this->assertEquals('Nice matching', $orElse);
        $this->assertEquals('Nice matching', $orReturn);
        $this->assertEquals('Nice matching', $orThrow);
    }

    private function matchGroup(): MatchGroup
    {
        return new MatchedGroup(
            new GroupDetails('first', 1, 'first', new MatchAllResults([], 'first')),
            new MatchedGroupOccurrence('Nice matching', 14)
        );
    }
}
