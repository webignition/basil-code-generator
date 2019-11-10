<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\DocBlockGenerator;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;

class DocBlockGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DocBlockGenerator
     */
    private $docBlockGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->docBlockGenerator = DocBlockGenerator::create();
    }

    /**
     * @dataProvider createFromBlockDataProvider
     */
    public function testCreateFromDocBlock(
        DocBlock $block,
        string $expectedCode
    ) {
        $code = $this->docBlockGenerator->createFromDocBlock($block);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createFromBlockDataProvider(): array
    {
        return [
            'empty' => [
                'block' => new DocBlock(),
                'expectedCode' =>
                    '/**' . "\n" .
                    ' */'
            ],
            'invalid content' => [
                'block' => new DocBlock([
                    new Statement('statement'),
                ]),
                'expectedCode' =>
                    '/**' . "\n" .
                    ' */'
            ],
            'empty line' => [
                'block' => new DocBlock([
                    new EmptyLine(),
                ]),
                'expectedCode' =>
                    '/**' . "\n" .
                    ' *' . "\n" .
                    ' */'
            ],
            'comments and empty lines' => [
                'block' => new DocBlock([
                    new Comment('start'),
                    new EmptyLine(),
                    new Comment('@dataProvider createFromBlockDataProvider')
                ]),
                'expectedCode' =>
                    '/**' . "\n" .
                    ' * start' . "\n" .
                    ' *' . "\n" .
                    ' * @dataProvider createFromBlockDataProvider' . "\n" .
                    ' */'
            ],
        ];
    }
}
