<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\LineList;

class MethodGenerator
{
    private $lineGenerator;

    public function __construct(LineGenerator $lineGenerator)
    {
        $this->lineGenerator = $lineGenerator;
    }

    public static function create(): MethodGenerator
    {
        return new MethodGenerator(
            LineGenerator::create()
        );
    }

    /**
     * @param MethodDefinitionInterface $methodDefinition
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromMethodDefinition(
        MethodDefinitionInterface $methodDefinition,
        array $variableIdentifiers = []
    ): string {
        $methodTemplate = <<<'EOD'
%s
{
%s
}
EOD;
        $signature = $this->createSignature($methodDefinition);
        $lines = $this->createCodeLines(new LineList($methodDefinition->getSources()), $variableIdentifiers);
        $lines = $this->indent($lines);

        return sprintf(
            $methodTemplate,
            $signature,
            implode("\n", $lines)
        );
    }

    private function createSignature(MethodDefinitionInterface $methodDefinition): string
    {
        $signature = $methodDefinition->getVisibility() . ' ';

        if ($methodDefinition->isStatic()) {
            $signature .= 'static ';
        }

        $arguments = $this->createSignatureArguments($methodDefinition->getArguments());
        $signature .= 'function ' . $methodDefinition->getName() . '(' . $arguments . ')';

        $returnType = $methodDefinition->getReturnType();

        if (null !== $returnType) {
            $signature .= ': ' . $returnType;
        }

        return $signature;
    }

    private function createSignatureArguments(array $argumentNames)
    {
        $arguments = $argumentNames;

        array_walk($arguments, function (&$argument) {
            $argument = '$' . $argument;
        });

        return implode(', ', $arguments);
    }

    /**
     * @param LineList $lineList
     * @param array $variableIdentifiers
     *
     * @return array
     *
     * @throws UnresolvedPlaceholderException
     */
    private function createCodeLines(LineList $lineList, array $variableIdentifiers): array
    {
        $lines = [];

        foreach ($lineList->getLines() as $line) {
            $lines[] = $this->lineGenerator->createFromLineObject($line, $variableIdentifiers);
        }

        return $lines;
    }

    private function indent(array $lines): array
    {
        return array_map(function ($line) {
            $line = '    ' . $line;

            if ('' === trim($line)) {
                $line = '';
            }

            return $line;
        }, $lines);
    }
}
