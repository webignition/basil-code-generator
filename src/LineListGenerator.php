<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\LineListInterface;

class LineListGenerator
{
    private $lineGenerator;

    public function __construct(LineGenerator $lineGenerator)
    {
        $this->lineGenerator = $lineGenerator;
    }

    public static function create(): LineListGenerator
    {
        return new LineListGenerator(
            LineGenerator::create()
        );
    }

    /**
     * @param LineListInterface $lineList
     * @param array $variableIdentifiers
     *
     * @return array
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromLineList(LineListInterface $lineList, array $variableIdentifiers = []): array
    {
        $lines = [];

        foreach ($lineList->getLines() as $line) {
            $lines[] = $this->lineGenerator->createFromLineObject($line, $variableIdentifiers);
        }

        return $lines;
    }
}
