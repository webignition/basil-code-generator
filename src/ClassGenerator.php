<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\ClassDefinitionInterface;

class ClassGenerator
{
    const CLASS_SIGNATURE_TEMPLATE = 'class %s %s';

    private $classDependencyHandler;
    private $methodGenerator;
    private $codeBlockGenerator;
    private $indenter;

    public function __construct(
        UseStatementFactory $classDependencyHandler,
        MethodGenerator $methodGenerator,
        CodeBlockGenerator $codeBlockGenerator,
        Indenter $indenter
    ) {
        $this->classDependencyHandler = $classDependencyHandler;
        $this->methodGenerator = $methodGenerator;
        $this->codeBlockGenerator = $codeBlockGenerator;
        $this->indenter = $indenter;
    }

    public static function create(): ClassGenerator
    {
        return new ClassGenerator(
            new UseStatementFactory(),
            MethodGenerator::create(),
            CodeBlockGenerator::create(),
            new Indenter()
        );
    }

    /**
     * @param ClassDefinitionInterface $classDefinition
     * @param string|null $baseClass
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        string $baseClass = null,
        array $variableIdentifiers = []
    ) {
        $classDependencies = $classDefinition->getMetadata()->getClassDependencies();
        $useStatements = $this->codeBlockGenerator->createWithUseStatementsFromLineList($classDependencies);

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
            $method = $this->indenter->indentContent($method);

            $methodCode[] = $method;
        }

        return implode("\n\n", $methodCode);
    }
}
