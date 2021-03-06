<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\LineInterface;
use webignition\BasilCompilationSource\Line\MethodInvocation\ObjectMethodInvocationInterface;
use webignition\BasilCompilationSource\Line\Statement;

class LineGenerator
{
    private $variablePlaceholderResolver;
    private $objectMethodGenerator;

    public function __construct(
        VariablePlaceholderResolver $variablePlaceholderResolver,
        ObjectMethodGenerator $objectMethodGenerator
    ) {
        $this->variablePlaceholderResolver = $variablePlaceholderResolver;
        $this->objectMethodGenerator = $objectMethodGenerator;
    }

    public static function create(): LineGenerator
    {
        return new LineGenerator(
            new VariablePlaceholderResolver(),
            ObjectMethodGenerator::create()
        );
    }

    /**
     * @param LineInterface $line
     * @param array<string, string> $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromLineObject(LineInterface $line, array $variableIdentifiers = []): string
    {
        $lineString = $this->createLine($line);

        if ($line instanceof Comment) {
            return $lineString;
        }

        return $this->variablePlaceholderResolver->resolve($lineString, $variableIdentifiers);
    }

    private function createLine(LineInterface $line): string
    {
        if ($line instanceof ClassDependency) {
            $line = new Statement(sprintf('use %s', (string) $line));
        }

        if ($line instanceof Comment) {
            return '// ' . $line->getContent();
        }

        if ($line instanceof Statement) {
            return $line->getContent() . ';';
        }

        if ($line instanceof ObjectMethodInvocationInterface) {
            return $this->objectMethodGenerator->createFromObjectMethodInvocation($line) . ';';
        }

        return '';
    }
}
