<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\BlockGenerator;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;

class BlockGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BlockGenerator
     */
    private $codeBlockGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeBlockGenerator = BlockGenerator::create();
    }

    /**
     * @dataProvider createFromBlockDataProvider
     */
    public function testCreateFromBlock(
        BlockInterface $block,
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
                'block' => new Block([
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
                'block' => new Block([
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
                'block' => new Block([
                    new Statement('$statement = new Statement("one")'),
                    new Statement('$block = new Block([$statement])'),
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    '$statement = new Statement("one");' . "\n" .
                    '$block = new Block([$statement]);'
            ],
            'has use statements' => [
                'block' => new Block([
                    new Statement(
                        '$statement = new Statement("one")',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(Statement::class),
                            ]))
                    ),
                    new Statement(
                        '$block = new Block([$statement])',
                        (new Metadata())
                            ->withClassDependencies(new ClassDependencyCollection([
                                new ClassDependency(Block::class),
                            ]))
                    ),
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use webignition\BasilCompilationSource\Line\Statement;' . "\n" .
                    'use webignition\BasilCompilationSource\Block\Block;' . "\n" .
                    '' . "\n" .
                    '$statement = new Statement("one");' . "\n" .
                    '$block = new Block([$statement]);'
            ],
        ];
    }
}
