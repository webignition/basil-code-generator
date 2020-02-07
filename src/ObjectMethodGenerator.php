<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\Line\MethodInvocation\ArgumentFormats;
use webignition\BasilCompilationSource\Line\MethodInvocation\ObjectMethodInvocationInterface;

class ObjectMethodGenerator
{
    private const STRING_PATTERN = '%s->%s(%s)';

    private $indenter;

    public function __construct(Indenter $indenter)
    {
        $this->indenter = $indenter;
    }

    public static function create(): ObjectMethodGenerator
    {
        return new ObjectMethodGenerator(
            new Indenter()
        );
    }

    public function createFromObjectMethodInvocation(ObjectMethodInvocationInterface $invocation): string
    {
        return sprintf(
            self::STRING_PATTERN,
            $invocation->getObject(),
            $invocation->getMethodName(),
            $this->createArgumentsString($invocation->getArguments(), $invocation->getArgumentFormat())
        );
    }

    /**
     * @param string[] $argumentStrings
     * @param int $format
     *
     * @return string
     */
    private function createArgumentsString(array $argumentStrings, int $format): string
    {
        $hasArguments = count($argumentStrings) > 0;

        if (!$hasArguments) {
            return '';
        }

        $argumentPrefix = '';
        $join = ', ';
        $stringSuffix = '';

        if (ArgumentFormats::STACKED === $format) {
            $argumentStrings = $this->indenter->indentLinesInArray($argumentStrings);

            $argumentPrefix = "\n";
            $join = ',';
            $stringSuffix = "\n";
        }

        array_walk($argumentStrings, function (&$argumentString) use ($argumentPrefix) {
            $argumentString = $argumentPrefix . $argumentString;
        });

        return implode($join, $argumentStrings) . $stringSuffix;
    }
}
