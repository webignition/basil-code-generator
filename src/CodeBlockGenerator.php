<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;

class CodeBlockGenerator
{
    private $lineGenerator;

    public function __construct(LineGenerator $lineGenerator)
    {
        $this->lineGenerator = $lineGenerator;
    }

    public static function create(): CodeBlockGenerator
    {
        return new CodeBlockGenerator(
            LineGenerator::create()
        );
    }

    /**
     * @param BlockInterface $block
     * @param array<string, string> $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromBlock(BlockInterface $block, array $variableIdentifiers = []): string
    {
        $lines = [];

        foreach ($block->getLines() as $line) {
            $lines[] = $this->lineGenerator->createFromLineObject($line, $variableIdentifiers);
        }

        return implode("\n", $lines);
    }

    /**
     * @param BlockInterface $block
     * @param array<string, string> $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createWithUseStatementsFromBlock(
        BlockInterface $block,
        array $variableIdentifiers = []
    ): string {
        $code = $block instanceof CodeBlockInterface
            ? $this->createFromBlock($block->getMetadata()->getClassDependencies())
            : '';

        if ('' !== $code) {
            $code .= "\n\n";
        }

        return $code . $this->createFromBlock($block, $variableIdentifiers);
    }
}
