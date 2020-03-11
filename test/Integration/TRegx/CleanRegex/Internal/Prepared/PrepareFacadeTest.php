<?php
namespace Test\Integration\TRegx\CleanRegex\Internal\Prepared;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TRegx\CleanRegex\Internal\Prepared\Parser\BindingParser;
use TRegx\CleanRegex\Internal\Prepared\Parser\InjectParser;
use TRegx\CleanRegex\Internal\Prepared\Parser\Parser;
use TRegx\CleanRegex\Internal\Prepared\Parser\PreparedParser;
use TRegx\CleanRegex\Internal\Prepared\PrepareFacade;

class PrepareFacadeTest extends TestCase
{
    /**
     * @test
     * @dataProvider standard
     * @param Parser $parser
     */
    public function test_standard(Parser $parser)
    {
        // given + when
        $pattern = (new PrepareFacade($parser, false))->getPattern();

        // then
        $this->assertEquals('/(I|We) want: User \(input\) :)/', $pattern);
    }

    public function standard(): array
    {
        return [
            'bind @' => [new BindingParser('(I|We) want: @input :)', ['input' => 'User (input)'])],
            'bind ``' => [new BindingParser('(I|We) want: `input` :)', ['input' => 'User (input)'])],
            'inject #' => [new InjectParser('(I|We) want: @ :)', ['User (input)'])],
            'prepared []' => [new PreparedParser(['(I|We) want: ', ['User (input)'], ' :)'])]
        ];
    }

    /**
     * @test
     * @dataProvider empty
     * @param Parser $parser
     */
    public function test_empty(Parser $parser)
    {
        // given + when
        $pattern = (new PrepareFacade($parser, false))->getPattern();

        // then
        $this->assertEquals('//', $pattern);
    }

    public function empty(): array
    {
        return [
            'bind @' => [new BindingParser('', [])],
            'inject # ' => [new InjectParser('', [])],
            'prepared \'\'' => [new PreparedParser([''])],
            'prepared []' => [new PreparedParser(['', []])],
        ];
    }

    /**
     * @test
     * @dataProvider pcre
     * @param Parser $parser
     * @param string $expected
     */
    public function test_ignoresPcre(Parser $parser, string $expected)
    {
        // given + when
        $pattern = (new PrepareFacade($parser, false))->getPattern();

        // then
        $this->assertEquals($expected, $pattern);
    }

    public function pcre(): array
    {
        return [
            'bind //' => [new BindingParser('//', []), '#//#'],
            'inject //' => [new InjectParser('//', []), '#//#'],
            'prepared //' => [new PreparedParser(['//']), '#//#'],

            'bind //mi' => [new BindingParser('//mi', []), '#//mi#'],
            'inject //mi' => [new InjectParser('//mi', []), '#//mi#'],
            'prepared //mi' => [new PreparedParser(['//mi']), '#//mi#'],
        ];
    }

    /**
     * @test
     * @dataProvider onlyUserInput
     * @param Parser $parser
     */
    public function test_onlyUserInput(Parser $parser)
    {
        // given + when
        $pattern = (new PrepareFacade($parser, false))->getPattern();

        // then
        $this->assertEquals('/%/', $pattern);
    }

    public function onlyUserInput(): array
    {
        return [
            'bind @' => [new BindingParser('@name', ['name' => '%'])],
            'inject # ' => [new InjectParser('@', ['%'])],
            'prepared []' => [new PreparedParser(['%'])],
        ];
    }

    /**
     * @test
     * @dataProvider delimiters
     * @param Parser $parser
     */
    public function test_quotesDelimiters(Parser $parser)
    {
        // given + when
        $pattern = (new PrepareFacade($parser, false))->getPattern();

        // then
        $this->assertEquals('%With delimiters / #Using / delimiters and \% :D%', $pattern);
    }

    public function delimiters(): array
    {
        return [
            'bind @' => [new BindingParser('With delimiters / #@input :D', ['input' => 'Using / delimiters and %'])],
            'inject #' => [new InjectParser('With delimiters / #@ :D', ['Using / delimiters and %'])],
            'prepared []' => [new PreparedParser(['With delimiters / #', ['Using / delimiters and %'], ' :D'])],
        ];
    }

    /**
     * @test
     * @dataProvider whitespace
     * @param Parser $parser
     */
    public function test_whitespace(Parser $parser)
    {
        // given + when
        $pattern = (new PrepareFacade($parser, false))->getPattern();

        // then
        $this->assertEquals('/(I|We) want: User \(input\)User \(input_2\)/', $pattern);
    }

    public function whitespace(): array
    {
        return [
            'bind @' => [new BindingParser('(I|We) want: @input@input_2', [
                'input' => 'User (input)',
                'input_2' => 'User (input_2)',
            ])],
            'inject #' => [new InjectParser('(I|We) want: @@', ['User (input)', 'User (input_2)'])],
            'prepared []' => [new PreparedParser(['(I|We) want: ', ['User (input)'], ['User (input_2)']])],
        ];
    }

    /**
     * @test
     * @dataProvider ignoredInputs
     * @param Parser $parser
     * @param string $expected
     */
    public function shouldIgnoreBindPlaceholders(Parser $parser, string $expected)
    {
        // given
        $facade = new PrepareFacade($parser, false);

        // when
        $pattern = $facade->getPattern();

        // then
        $this->assertEquals($expected, $pattern);
    }

    public function ignoredInputs(): array
    {
        return [
            [
                // Should allow for inserting @ placeholders again
                new BindingParser('(I|We) would like to match: @input (and|or) @input2', [
                    'input' => '@input',
                    'input2' => '@input2',
                ]),
                '/(I|We) would like to match: @input (and|or) @input2/',
            ],
            [
                // Should allow for inserting `` placeholders again
                new BindingParser('(I|We) would like to match: `input` (and|or) `input2`', [
                    'input' => '`input`',
                    'input2' => '`input2`',
                ]),
                '/(I|We) would like to match: `input` (and|or) `input2`/',
            ],
            [
                // Should ignore @@ placeholders
                new BindingParser('(I|We) would like to match: @input (and|or) @input_2@', [
                    0 => 'input',
                    1 => 'input_2',
                ]),
                '/(I|We) would like to match: @input (and|or) @input_2@/',
            ]
        ];
    }

    /**
     * @test
     * @dataProvider invalidInputs
     * @param Parser $parser
     * @param string $message
     */
    public function shouldThrow_onInvalidInput(Parser $parser, string $message)
    {
        // given
        $facade = new PrepareFacade($parser, false);

        // then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        // when
        $facade->getPattern();
    }

    public function invalidInputs(): array
    {
        return [
            [
                new BindingParser('@placeholder', []),
                "Could not find a corresponding value for placeholder 'placeholder'",
            ],
            [
                new BindingParser('@input1 and @input2', ['input1']),
                "Could not find a corresponding value for placeholder 'input2'",
            ],
            [
                new BindingParser('@input', ['input', 'input2']),
                "Could not find a corresponding placeholder for name 'input2'",
            ],
            [
                new BindingParser('@input', ['input' => 'some value', 0 => 'input']),
                "Name 'input' is used more than once (as a key or as ignored value)",
            ],
            [
                new BindingParser('@input', ['input' => 4]),
                "Invalid bound value for name 'input'. Expected string, but integer (4) given",
            ],
            [
                new BindingParser('@input', ['input' => []]),
                "Invalid bound value for name 'input'. Expected string, but array (0) given",
            ],
            [
                new BindingParser('well', [0 => 21]),
                'Invalid bound parameters. Expected string, but integer (21) given',
            ],
            [
                new BindingParser('well', ['(asd)' => 21]),
                "Invalid name '(asd)'. Expected a string consisting only of alphanumeric characters and an underscore [a-zA-Z0-9_]",
            ],

            [
                new InjectParser('@', []),
                "Could not find a corresponding value for placeholder #0",
            ],
            [
                new InjectParser('@ and @', ['input']),
                "Could not find a corresponding value for placeholder #1",
            ],
            [
                new InjectParser('@', ['input', 'input2']),
                "Superfluous bind value [integer (1) => string ('input2')]",
            ],
            [
                new InjectParser('@@@', ['', '', 'foo' => 4]),
                "Invalid inject value for key - string ('foo'). Expected string, but integer (4) given",
            ],

            [
                new PreparedParser(['input', 5]),
                'Invalid prepared pattern part. Expected string, but integer (5) given',
            ],
            [
                new PreparedParser(['input', [4], 'input']),
                'Invalid bound value. Expected string, but integer (4) given',
            ],
            [
                new PreparedParser(['input', [[]], 'input']),
                'Invalid bound value. Expected string, but array (0) given',
            ],
            [
                new PreparedParser([]),
                'Empty array of prepared pattern parts',
            ],
        ];
    }
}
