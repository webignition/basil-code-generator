<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\LineListGenerator;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\Statement;

class LineListGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LineListGenerator
     */
    private $lineListGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lineListGenerator = LineListGenerator::create();
    }

    /**
     * @dataProvider createFromLineListDataProvider
     */
    public function testCreateFromLineList(
        LineListInterface $lineList,
        array $variableIdentifiers,
        array $expectedLines
    ) {
        $code = $this->lineListGenerator->createFromLineList($lineList, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedLines, $code);
    }

    public function createFromLineListDataProvider(): array
    {
        return [
            'no variable identifiers' => [
                'lineList' => new LineList([
                    new Comment('Add x and y and then return'),
                    new Statement('$z = $x + $y'),
                    new EmptyLine(),
                    new Statement('return $z'),
                ]),
                'variableIdentifiers' => [],
                'expectedLines' => [
                    '// Add x and y and then return',
                    '$z = $x + $y;',
                    '',
                    'return $z;',
                ]
            ],
            'has variable identifiers' => [
                'lineList' => new LineList([
                    new Comment('Add {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }} and then return'),
                    new Statement('$z = {{ PLACEHOLDER1 }} + {{ PLACEHOLDER2 }}'),
                    new EmptyLine(),
                    new Statement('return $z'),
                ]),
                'variableIdentifiers' => [
                    'PLACEHOLDER1' => '$x',
                    'PLACEHOLDER2' => '$y',
                ],
                'expectedLines' => [
                    '// Add $x and $y and then return',
                    '$z = $x + $y;',
                    '',
                    'return $z;',
                ]
            ],
        ];
    }
}
