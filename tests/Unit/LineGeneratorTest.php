<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\LineGenerator;
use webignition\BasilCodeGenerator\UnresolvedPlaceholderException;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineInterface;
use webignition\BasilCompilationSource\Statement;

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
            'comment with placeholder' => [
                'line' => new Comment('comment content with {{ PLACEHOLDER }}'),
                'variableIdentifiers' => [
                    'PLACEHOLDER' => 'replaced content'
                ],
                'expectedLine' => '// comment content with replaced content',
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
        ];
    }

    public function testCreateFromLineObjectThrowsUnresolvedPlaceholderException()
    {
        try {
            $this->lineGenerator->createFromLineObject(new Comment('Content with {{ PLACEHOLDER }}'), []);
        } catch (UnresolvedPlaceholderException $unresolvedPlaceholderException) {
            $this->assertSame(
                'Unresolved placeholder "PLACEHOLDER" in content "// Content with {{ PLACEHOLDER }}"',
                $unresolvedPlaceholderException->getMessage()
            );

            $this->assertSame('PLACEHOLDER', $unresolvedPlaceholderException->getPlaceholder());
            $this->assertSame('// Content with {{ PLACEHOLDER }}', $unresolvedPlaceholderException->getContent());
        }
    }
}
