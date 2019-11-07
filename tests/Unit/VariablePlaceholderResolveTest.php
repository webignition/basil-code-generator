<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\UnresolvedPlaceholderException;
use webignition\BasilCodeGenerator\VariablePlaceholderResolver;

class VariablePlaceholderResolveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VariablePlaceholderResolver
     */
    private $variablePlaceholderResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->variablePlaceholderResolver = new VariablePlaceholderResolver();
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $content, array $variableIdentifiers, string $expectedResolvedContent)
    {
        $resolvedContent = $this->variablePlaceholderResolver->resolve($content, $variableIdentifiers);

        $this->assertSame($expectedResolvedContent, $resolvedContent);
    }

    public function resolveDataProvider(): array
    {
        return [
            'empty content, no placeholders' => [
                'content' => '',
                'variableIdentifiers' => [],
                'expectedResolvedContent' => '',
            ],
            'non-empty content, no placeholders' => [
                'content' => 'non-empty content',
                'variableIdentifiers' => [],
                'expectedResolvedContent' => 'non-empty content',
            ],
            'non-empty content, has placeholders' => [
                'content' => '{{ PLACEHOLDER1 }}->method({{ PLACEHOLDER2 }})',
                'variableIdentifiers' => [
                    'PLACEHOLDER1' => '$this',
                    'PLACEHOLDER2' => '$argument',
                ],
                'expectedResolvedContent' => '$this->method($argument)',
            ],
        ];
    }

    public function testResolveThrowsUnresolvedPlaceholderException()
    {
        try {
            $this->variablePlaceholderResolver->resolve('Content with {{ PLACEHOLDER }}', []);
        } catch (UnresolvedPlaceholderException $unresolvedPlaceholderException) {
            $this->assertSame(
                'Unresolved placeholder "PLACEHOLDER" in content "Content with {{ PLACEHOLDER }}"',
                $unresolvedPlaceholderException->getMessage()
            );

            $this->assertSame('PLACEHOLDER', $unresolvedPlaceholderException->getPlaceholder());
            $this->assertSame('Content with {{ PLACEHOLDER }}', $unresolvedPlaceholderException->getContent());
        }
    }
}
