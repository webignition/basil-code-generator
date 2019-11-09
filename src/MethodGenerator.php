<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\MethodDefinitionInterface;

class MethodGenerator
{
    private $codeBlockGenerator;
    private $indenter;

    public function __construct(CodeBlockGenerator $codeBlockGenerator, Indenter $indenter)
    {
        $this->codeBlockGenerator = $codeBlockGenerator;
        $this->indenter = $indenter;
    }

    public static function create(): MethodGenerator
    {
        return new MethodGenerator(
            CodeBlockGenerator::create(),
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

        $lines = $this->codeBlockGenerator->createFromLineList($methodDefinition, $variableIdentifiers);
        $lines = $this->indenter->indentContent($lines);

        return sprintf($methodTemplate, $signature, $lines);
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
