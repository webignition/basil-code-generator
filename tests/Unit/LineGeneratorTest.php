<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\LineGenerator;
use webignition\BasilCodeGenerator\UnresolvedPlaceholderException;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\LineInterface;
use webignition\BasilCompilationSource\Line\MethodInvocation\ArgumentFormats;
use webignition\BasilCompilationSource\Line\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilationSource\Line\Statement;

class LineGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LineGenerator
     */
    private $lineGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lineGenerator = LineGenerator::create();
    }

    /**
     * @dataProvider createFromLineObjectDataProvider
     *
     * @param LineInterface $line
     * @param array<string, string> $variableIdentifiers
     * @param string $expectedLine
     * @throws UnresolvedPlaceholderException
     */
    public function testCreateFromLineObject(LineInterface $line, array $variableIdentifiers, string $expectedLine)
    {
        $lineString = $this->lineGenerator->createFromLineObject($line, $variableIdentifiers);

        $this->assertSame($expectedLine, $lineString);
    }

    public function createFromLineObjectDataProvider(): array
    {
        return [
            'empty line' => [
                'line' => new EmptyLine(),
                'variableIdentifiers' => [],
                'expectedLine' => '',
            ],
            'comment' => [
                'line' => new Comment('comment content'),
                'variableIdentifiers' => [],
                'expectedLine' => '// comment content',
            ],
            'comment with placeholder is not replaced' => [
                'line' => new Comment('comment content with {{ PLACEHOLDER }}'),
                'variableIdentifiers' => [
                    'PLACEHOLDER' => 'replaced content'
                ],
                'expectedLine' => '// comment content with {{ PLACEHOLDER }}',
            ],
            'statement' => [
                'line' => new Statement('$x'),
                'variableIdentifiers' => [],
                'expectedLine' => '$x;',
            ],
            'statement with placeholder' => [
                'line' => new Statement('{{ PLACEHOLDER1 }} = {{ PLACEHOLDER2 }}'),
                'variableIdentifiers' => [
                    'PLACEHOLDER1' => '$x',
                    'PLACEHOLDER2' => '$y',
                ],
                'expectedLine' => '$x = $y;',
            ],
            'use statement' => [
                'line' => new ClassDependency(ClassDependency::class),
                'variableIdentifiers' => [],
                'expectedLine' => 'use webignition\BasilCompilationSource\Line\ClassDependency;',
            ],
            'object method invocation, no arguments' => [
                'line' => new ObjectMethodInvocation('object', 'methodName'),
                'variableIdentifiers' => [],
                'expectedLine' => 'object->methodName()',
            ],
            'object method invocation, inline arguments' => [
                'line' => new ObjectMethodInvocation(
                    'object',
                    'methodName',
                    [
                        '1',
                        "\'single-quoted value\'",
                    ]
                ),
                'variableIdentifiers' => [],
                'expectedLine' => "object->methodName(1, \'single-quoted value\')",
            ],
            'object method invocation, stacked arguments' => [
                'line' => new ObjectMethodInvocation(
                    'object',
                    'methodName',
                    [
                        '1',
                        "\'single-quoted value\'",
                    ],
                    ArgumentFormats::STACKED
                ),
                'variableIdentifiers' => [],
                'expectedLine' => "object->methodName(\n" .
                    "    1,\n" .
                    "    \'single-quoted value\'\n" .
                    ")",
            ],
        ];
    }

    public function testCreateFromLineObjectThrowsUnresolvedPlaceholderException()
    {
        try {
            $this->lineGenerator->createFromLineObject(new Statement('Content with {{ PLACEHOLDER }}'), []);
        } catch (UnresolvedPlaceholderException $unresolvedPlaceholderException) {
            $this->assertSame(
                'Unresolved placeholder "PLACEHOLDER" in content "Content with {{ PLACEHOLDER }};"',
                $unresolvedPlaceholderException->getMessage()
            );

            $this->assertSame('PLACEHOLDER', $unresolvedPlaceholderException->getPlaceholder());
            $this->assertSame('Content with {{ PLACEHOLDER }};', $unresolvedPlaceholderException->getContent());
        }
    }
}
