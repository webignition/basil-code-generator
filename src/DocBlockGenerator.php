<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;

class DocBlockGenerator
{
    private const TEMPLATE = <<< EOD
/**
%s
 */
EOD;

    private const EMPTY = <<< EOD
/**
 */
EOD;

    public static function create(): DocBlockGenerator
    {
        return new DocBlockGenerator();
    }

    public function createFromDocBlock(DocBlock $block): string
    {
        $blockLines = $block->getLines();
        if (0 === count($blockLines)) {
            return self::EMPTY;
        }

        $lines = [];

        foreach ($block->getLines() as $line) {
            if ($line instanceof Comment || $line instanceof EmptyLine) {
                $content = ' *';

                if ($line instanceof Comment) {
                    $content .= ' ' . $line->getContent();
                }

                $lines[] = $content;
            }
        }

        return sprintf(self::TEMPLATE, implode("\n", $lines));
    }
}
