<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;

class ClassGenerator
{
    private const CLASS_SIGNATURE_TEMPLATE = 'class %s %s';
    private const NAMESPACE_SEPARATOR = '\\';

    private $methodGenerator;
    private $codeBlockGenerator;
    private $indenter;

    public function __construct(
        MethodGenerator $methodGenerator,
        CodeBlockGenerator $codeBlockGenerator,
        Indenter $indenter
    ) {
        $this->methodGenerator = $methodGenerator;
        $this->codeBlockGenerator = $codeBlockGenerator;
        $this->indenter = $indenter;
    }

    public static function create(): ClassGenerator
    {
        return new ClassGenerator(
            MethodGenerator::create(),
            CodeBlockGenerator::create(),
            new Indenter()
        );
    }

    /**
     * @param ClassDefinitionInterface $classDefinition
     * @param string $fullyQualifiedBaseClass
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        string $fullyQualifiedBaseClass,
        array $variableIdentifiers = []
    ) {
        $baseClass = $this->createBaseClassName($fullyQualifiedBaseClass);
        $baseClassDependency = $this->createBaseClassDependency($fullyQualifiedBaseClass);

        $classDependencies = $classDefinition->getMetadata()->getClassDependencies();
        if ($baseClassDependency instanceof ClassDependency) {
            $classDependencies->addLine($baseClassDependency);
        }

        $useStatements = $this->codeBlockGenerator->createFromBlock($classDependencies);

        $signature = $this->createClassSignatureLine($classDefinition->getName(), $baseClass);
        $body = $this->createClassBody($classDefinition->getMethods(), $variableIdentifiers);

        $classTemplate = <<<'EOD'
%s

%s
{
%s
}
EOD;

        return trim(sprintf($classTemplate, $useStatements, $signature, $body));
    }

    private function createClassSignatureLine(string $className, ?string $baseClass)
    {
        $extendsSegment = null === $baseClass
             ? ''
            : 'extends ' . $baseClass;

        return trim(sprintf(self::CLASS_SIGNATURE_TEMPLATE, $className, $extendsSegment));
    }

    /**
     * @param array $methods
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    private function createClassBody(array $methods, array $variableIdentifiers): string
    {
        $methodCode = [];

        foreach ($methods as $methodDefinition) {
            $method = $this->methodGenerator->createFromMethodDefinition($methodDefinition, $variableIdentifiers);
            $method = $this->indenter->indent($method);

            $methodCode[] = $method;
        }

        return implode("\n\n", $methodCode);
    }

    private function createBaseClassName(string $fullyQualifiedBaseClass): string
    {
        $classNameParts = explode(self::NAMESPACE_SEPARATOR, $fullyQualifiedBaseClass);

        if (0 === count($classNameParts)) {
            return $fullyQualifiedBaseClass;
        }

        return array_pop($classNameParts);
    }

    private function createBaseClassDependency(string $fullyQualifiedBaseClass): ?ClassDependency
    {
        if (0 === substr_count($fullyQualifiedBaseClass, self::NAMESPACE_SEPARATOR)) {
            return null;
        }

        return new ClassDependency($fullyQualifiedBaseClass);
    }
}
