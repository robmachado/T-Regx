<?php
namespace Test\Unit\TRegx\CleanRegex\Match\MatchPattern\forFirst;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Exception\SubjectNotMatchedException;
use TRegx\CleanRegex\Internal\InternalPattern;
use TRegx\CleanRegex\Match\Details\Match;
use TRegx\CleanRegex\Match\Details\NotMatched;
use TRegx\CleanRegex\Match\MatchPattern;

class MatchPatternTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetMatch_withDetails()
    {
        // given
        $pattern = $this->getMatchPattern("Nice matching pattern");

        // when
        $pattern
            ->forFirst(function (Match $match) {
                // then
                $this->assertEquals(0, $match->index());
                $this->assertEquals("Nice matching pattern", $match->subject());
                $this->assertEquals(['Nice', 'matching', 'pattern'], $match->all());
                $this->assertEquals(['N'], $match->groups()->texts());
            })
            ->orThrow();
    }

    /**
     * @test
     */
    public function shouldGetMatch_withoutCollapsingOrMethod()
    {
        // given
        $pattern = $this->getMatchPattern("Nice matching pattern");

        // when
        $pattern
            ->forFirst(function (Match $match) {
                // then
                $this->assertEquals("Nice matching pattern", $match->subject());
            });
        // ->orThrow();
    }

    /**
     * @test
     */
    public function shouldGetFirst()
    {
        // given
        $pattern = $this->getMatchPattern("Nice matching pattern");

        // when
        $first1 = $pattern->forFirst('strtoupper')->orReturn(null);
        $first2 = $pattern->forFirst('strtoupper')->orThrow();
        $first3 = $pattern->forFirst('strtoupper')->orElse(function () {
        });

        // then
        $this->assertEquals('NICE', $first1);
        $this->assertEquals('NICE', $first2);
        $this->assertEquals('NICE', $first3);
    }

    /**
     * @test
     */
    public function shouldNotInvokeFirst_onNotMatchingSubject()
    {
        // given
        $pattern = $this->getMatchPattern('NOT MATCHING');

        // when
        $pattern->forFirst($this->failCallback())->orReturn(null);
        $pattern->forFirst($this->failCallback())->orElse(function () {
        });
        try {
            @$pattern->forFirst($this->failCallback())->orThrow();
        } catch (SubjectNotMatchedException $ignored) {
        }

        // then
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function should_onNotMatchingSubject_throw()
    {
        // given
        $pattern = $this->getMatchPattern('NOT MATCHING');

        // then
        $this->expectException(SubjectNotMatchedException::class);

        // when
        $pattern->forFirst('strrev')->orThrow();
    }

    /**
     * @test
     */
    public function should_onNotMatchingSubject_throw_userGivenException()
    {
        // given
        $pattern = $this->getMatchPattern('NOT MATCHING');

        // then
        $this->expectException(InvalidArgumentException::class);

        // when
        $pattern->forFirst('strrev')->orThrow(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function should_onNotMatchingSubject_throw_withMessage()
    {
        // given
        $pattern = $this->getMatchPattern('NOT MATCHING');

        // then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected to get the first match, but subject was not matched');

        // when
        $pattern->forFirst('strrev')->orThrow(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function should_onNotMatchingSubject_getDefault()
    {
        // given
        $pattern = $this->getMatchPattern('NOT MATCHING');

        // when
        $value = $pattern->forFirst('strrev')->orReturn('def');

        // then
        $this->assertEquals('def', $value);
    }

    /**
     * @test
     */
    public function should_onNotMatchingSubject_call()
    {
        // given
        $pattern = $this->getMatchPattern('NOT MATCHING');

        // when
        $value = $pattern->forFirst('strrev')->orElse(function () {
            return 'new value';
        });

        // then
        $this->assertEquals('new value', $value);
    }

    /**
     * @test
     */
    public function should_onNotMatchingSubject_call_withDetails()
    {
        // given
        $pattern = new MatchPattern(InternalPattern::standard("(?:[A-Z])?[a-z']+ (?<group>.)"), 'NOT MATCHING');

        // when
        $pattern->forFirst('strrev')->orElse(function (NotMatched $details) {
            // then
            $this->assertEquals('NOT MATCHING', $details->subject());
            $this->assertEquals(['group'], $details->groupNames());
            $this->assertTrue($details->hasGroup('group'));
            $this->assertTrue($details->hasGroup(0));
            $this->assertTrue($details->hasGroup(1));
            $this->assertFalse($details->hasGroup('other'));
            $this->assertFalse($details->hasGroup(2));
        });
    }

    private function getMatchPattern($subject): MatchPattern
    {
        return new MatchPattern(InternalPattern::standard("([A-Z])?[a-z']+"), $subject);
    }

    private function failCallback(): callable
    {
        return function () {
            $this->fail("Failed asserting that forFirst() is not invoked for not matching subject");
        };
    }
}
