<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\LineListInterface;

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
     * @param LineListInterface $lineList
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromLineList(LineListInterface $lineList, array $variableIdentifiers = []): string
    {
        $lines = [];

        foreach ($lineList->getLines() as $line) {
            $lines[] = $this->lineGenerator->createFromLineObject($line, $variableIdentifiers);
        }

        return implode("\n", $lines);
    }

    /**
     * @param LineListInterface $lineList
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createWithUseStatementsFromLineList(
        LineListInterface $lineList,
        array $variableIdentifiers = []
    ): string {
        $code = $this->createFromLineList($lineList->getMetadata()->getClassDependencies());

        if ('' !== $code) {
            $code .= "\n\n";
        }

        return $code . $this->createFromLineList($lineList, $variableIdentifiers);
    }
}
