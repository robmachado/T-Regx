<?php
namespace Test\Integration\TRegx\CleanRegex;

use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Pattern;

class PatternTest extends TestCase
{
    /**
     * @test
     * @dataProvider patterns
     * @param string $pattern
     * @param bool $expected
     * @param bool $_
     */
    public function testStandard(string $pattern, bool $expected, bool $_)
    {
        // given
        $pattern = Pattern::of($pattern);

        // when
        $valid = $pattern->valid();

        // then
        $this->assertEquals($expected, $valid);
    }

    /**
     * @test
     * @dataProvider patterns
     * @param string $pattern
     * @param bool $_
     * @param bool $expected
     */
    public function testPcre(string $pattern, bool $_, bool $expected)
    {
        // given
        $pattern = Pattern::pcre($pattern);

        // when
        $valid = $pattern->valid();

        // then
        $this->assertEquals($expected, $valid);
    }

    public function patterns(): array
    {
        return [
            'of'           => ['Foo', true, false],
            'pcre'         => ['/Foo/', true, true],
            'pcre,invalid' => ['/invalid)/', false, false],
            'invalid'      => ['invalid)', false, false],
            'empty'        => ['', true, false],
        ];
    }
}
