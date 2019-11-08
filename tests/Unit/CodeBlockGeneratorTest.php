<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\CodeBlockGenerator;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;

class CodeBlockGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CodeBlockGenerator
     */
    private $codeBlockGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeBlockGenerator = CodeBlockGenerator::create();
    }

    /**
     * @dataProvider createFromLineListDataProvider
     */
    public function testCreateFromLineList(
        LineListInterface $lineList,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->codeBlockGenerator->createFromLineList($lineList, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
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
                'expectedCode' =>
                    '// Add x and y and then return' . "\n" .
                    '$z = $x + $y;' . "\n" .
                    '' . "\n" .
                    'return $z;'
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
                'expectedLines' =>
                    '// Add $x and $y and then return' . "\n" .
                    '$z = $x + $y;' . "\n" .
                    '' . "\n" .
                    'return $z;'
            ],
        ];
    }

    /**
     * @dataProvider createWithUseStatementsFromLineListDataProvider
     */
    public function testCreateWithUseStatementsFromLineList(
        LineListInterface $lineList,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->codeBlockGenerator->createWithUseStatementsFromLineList($lineList, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createWithUseStatementsFromLineListDataProvider(): array
    {
        return [
            'no use statements' => [
                'lineList' => new LineList([
                    new Statement('$statement = new Statement("one")'),
                    new Statement('$lineList = new LineList([$statement])'),
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    '$statement = new Statement("one");' . "\n" .
                    '$lineList = new LineList([$statement]);'
            ],
            'has use statements' => [
                'lineList' => new LineList([
                    new Statement(
                        '$statement = new Statement("one")',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(Statement::class),
                            ]))
                    ),
                    new Statement(
                        '$lineList = new LineList([$statement])',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(LineList::class),
                            ]))
                    ),
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use webignition\BasilCompilationSource\Statement;' . "\n" .
                    'use webignition\BasilCompilationSource\LineList;' . "\n" .
                    '' . "\n" .
                    '$statement = new Statement("one");' . "\n" .
                    '$lineList = new LineList([$statement]);'
            ],
        ];
    }
}
