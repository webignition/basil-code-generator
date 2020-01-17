<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\CodeBlockGenerator;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;

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
     * @dataProvider createFromBlockDataProvider
     *
     * @param CodeBlockInterface $block
     * @param array<string, string> $variableIdentifiers
     * @param string $expectedCode
     */
    public function testCreateFromBlock(
        CodeBlockInterface $block,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->codeBlockGenerator->createFromBlock($block, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createFromBlockDataProvider(): array
    {
        return [
            'no variable identifiers' => [
                'block' => new CodeBlock([
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
                'block' => new CodeBlock([
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
                    '// Add {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }} and then return' . "\n" .
                    '$z = $x + $y;' . "\n" .
                    '' . "\n" .
                    'return $z;'
            ],
        ];
    }

    /**
     * @dataProvider createWithUseStatementsFromLineListDataProvider
     *
     * @param BlockInterface $block
     * @param array<string, string> $variableIdentifiers
     * @param string $expectedCode
     */
    public function testCreateWithUseStatementsFromLineList(
        BlockInterface $block,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->codeBlockGenerator->createWithUseStatementsFromBlock($block, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createWithUseStatementsFromLineListDataProvider(): array
    {
        return [
            'no use statements' => [
                'block' => new CodeBlock([
                    new Statement('$statement = new Statement("one")'),
                    new Statement('$block = new CodeBlock([$statement])'),
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    '$statement = new Statement("one");' . "\n" .
                    '$block = new CodeBlock([$statement]);'
            ],
            'has use statements' => [
                'block' => new CodeBlock([
                    new Statement(
                        '$statement = new Statement("one")',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(Statement::class),
                            ]))
                    ),
                    new Statement(
                        '$block = new CodeBlock([$statement])',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(CodeBlock::class),
                            ]))
                    ),
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use webignition\BasilCompilationSource\Line\Statement;' . "\n" .
                    'use webignition\BasilCompilationSource\Block\CodeBlock;' . "\n" .
                    '' . "\n" .
                    '$statement = new Statement("one");' . "\n" .
                    '$block = new CodeBlock([$statement]);'
            ],
        ];
    }
}
