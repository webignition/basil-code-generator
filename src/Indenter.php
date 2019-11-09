<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

class Indenter
{
    private const LINE_DELIMITER = "\n";

    public function indent(string $content, int $spaceCount = 4): string
    {
        $lines = explode(self::LINE_DELIMITER, $content);

        $indentedLines = array_map(
            function ($line) use ($spaceCount) {
                if ('' === $line) {
                    return '';
                }

                $indentation = str_repeat(' ', $spaceCount);

                return $indentation . $line;
            },
            $lines
        );

        return implode(self::LINE_DELIMITER, $indentedLines);
    }
}
