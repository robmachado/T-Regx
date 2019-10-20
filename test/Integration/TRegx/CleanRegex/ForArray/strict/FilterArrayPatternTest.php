<?php
namespace Test\Integration\TRegx\CleanRegex\ForArray\strict;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Test\DataProviders;
use TRegx\CrossData\CrossDataProviders;

class FilterArrayPatternTest extends TestCase
{
    /**
     * @test
     * @dataProvider filterMethods
     * @param string $method
     * @param null|int|array|callable|resource $listElement
     * @param string $type
     */
    public function test(string $method, $listElement, string $type)
    {
        // given
        $input = ['Foo', $listElement];

        // then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Only elements of type `string` can be filtered, but $type given");

        // when
        pattern('')->forArray($input)->strict()->$method();
    }

    function filterMethods(): array
    {
        return CrossDataProviders::cross(
            [['filter'], ['filterAssoc']],
            DataProviders::allPhpTypes('string', 'int')
        );
    }
}
