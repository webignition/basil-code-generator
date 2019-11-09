<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\Block\BlockInterface;

class BlockGenerator
{
    private $lineGenerator;

    public function __construct(LineGenerator $lineGenerator)
    {
        $this->lineGenerator = $lineGenerator;
    }

    public static function create(): BlockGenerator
    {
        return new BlockGenerator(
            LineGenerator::create()
        );
    }

    /**
     * @param BlockInterface $block
     * @param array $variableIdentifiers
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
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createWithUseStatementsFromBlock(
        BlockInterface $block,
        array $variableIdentifiers = []
    ): string {
        $code = $this->createFromBlock($block->getMetadata()->getClassDependencies());

        if ('' !== $code) {
            $code .= "\n\n";
        }

        return $code . $this->createFromBlock($block, $variableIdentifiers);
    }
}
