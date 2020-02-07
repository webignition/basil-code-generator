<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;

class MethodGenerator
{
    private $codeBlockGenerator;
    private $indenter;
    private $docBlockGenerator;

    public function __construct(
        CodeBlockGenerator $codeBlockGenerator,
        Indenter $indenter,
        DocBlockGenerator $docBlockGenerator
    ) {
        $this->codeBlockGenerator = $codeBlockGenerator;
        $this->indenter = $indenter;
        $this->docBlockGenerator = $docBlockGenerator;
    }

    public static function create(): MethodGenerator
    {
        return new MethodGenerator(
            CodeBlockGenerator::create(),
            new Indenter(),
            DocBlockGenerator::create()
        );
    }

    /**
     * @param MethodDefinitionInterface $methodDefinition
     * @param array<string, string> $variableIdentifiers
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

        $lines = $this->codeBlockGenerator->createFromBlock($methodDefinition, $variableIdentifiers);
        $lines = $this->indenter->indentLinesInString($lines);
        $lines = rtrim($lines, "\n");

        $content = sprintf($methodTemplate, $signature, $lines);

        $docBlock = $methodDefinition->getDocBlock();
        if (count($docBlock->getLines()) > 0) {
            $docblockContent = $this->docBlockGenerator->createFromDocBlock($docBlock);

            $content = $docblockContent . "\n" . $content;
        }

        return $content;
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

    /**
     * @param array<string, string> $argumentNames
     *
     * @return string
     */
    private function createSignatureArguments(array $argumentNames): string
    {
        $arguments = $argumentNames;

        array_walk($arguments, function (&$argument) {
            $argument = '$' . $argument;
        });

        return implode(', ', $arguments);
    }
}
