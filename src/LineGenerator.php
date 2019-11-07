<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\LineInterface;
use webignition\BasilCompilationSource\Statement;

class LineGenerator
{
    private $variablePlaceholderResolver;

    public function __construct(VariablePlaceholderResolver $variablePlaceholderResolver)
    {
        $this->variablePlaceholderResolver = $variablePlaceholderResolver;
    }

    public static function create(): LineGenerator
    {
        return new LineGenerator(
            new VariablePlaceholderResolver()
        );
    }

    /**
     * @param LineInterface $line
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromLineObject(LineInterface $line, array $variableIdentifiers = []): string
    {
        $lineString = $this->createLine($line);

        return $this->variablePlaceholderResolver->resolve($lineString, $variableIdentifiers);
    }

    private function createLine(LineInterface $line): string
    {
        if ($line instanceof Comment) {
            return '// ' . $line->getContent();
        }

        if ($line instanceof Statement) {
            return '' . $line->getContent() . ';';
        }

        return '';
    }
}
