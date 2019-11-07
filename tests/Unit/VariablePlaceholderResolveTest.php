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

    /**
     * @dataProvider resolveThrowsUnresolvedPlaceholderExceptionDataProvider
     */
    public function testResolveThrowsUnresolvedPlaceholderException(
        string $content,
        array $variableIdentifiers,
        string $expectedPlaceholder
    ) {
        try {
            $this->variablePlaceholderResolver->resolve($content, $variableIdentifiers);
        } catch (UnresolvedPlaceholderException $unresolvedPlaceholderException) {
            $this->assertSame($expectedPlaceholder, $unresolvedPlaceholderException->getPlaceholder());
            $this->assertSame($content, $unresolvedPlaceholderException->getContent());
        }
    }

    public function resolveThrowsUnresolvedPlaceholderExceptionDataProvider(): array
    {
        return [
            'single placeholder' => [
                'content' => 'Content with {{ PLACEHOLDER }}',
                'variableIdentifiers' => [],
                'expectedPlaceholder' => 'PLACEHOLDER',
            ],
            'two placeholders, both missing' => [
                'content' => 'Content with {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }}',
                'variableIdentifiers' => [],
                'expectedPlaceholder' => 'PLACEHOLDER1',
            ],
            'two placeholders, first missing' => [
                'content' => 'Content with {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }}',
                'variableIdentifiers' => [
                    'PLACEHOLDER2' => '$y',
                ],
                'expectedPlaceholder' => 'PLACEHOLDER1',
            ],
            'two placeholders, second missing' => [
                'content' => 'Content with {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }}',
                'variableIdentifiers' => [
                    'PLACEHOLDER1' => '$x',
                ],
                'expectedPlaceholder' => 'PLACEHOLDER2',
            ],
        ];
    }
}
