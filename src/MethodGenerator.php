<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\LineList;

class MethodGenerator
{
    private $lineListGenerator;
    private $indenter;

    public function __construct(LineListGenerator $lineListGenerator, Indenter $indenter)
    {
        $this->lineListGenerator = $lineListGenerator;
        $this->indenter = $indenter;
    }

    public static function create(): MethodGenerator
    {
        return new MethodGenerator(
            LineListGenerator::create(),
            new Indenter()
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
        $lines = $this->lineListGenerator->createFromLineList(
            new LineList($methodDefinition->getSources()),
            $variableIdentifiers
        );

        $lines = $this->indenter->indentLines($lines);

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
}
