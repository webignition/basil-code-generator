<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementInterface;

class UseStatementFactory
{
    const TEMPLATE = 'use %s';

    public function createSource(ClassDependency $classDependency): StatementInterface
    {
        return new Statement(sprintf(self::TEMPLATE, (string) $classDependency));
    }
}
