<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenTypeEnum;

final readonly class Carve2CommentBodyPattern implements Pattern
{
    /**
     * @return array{match?: array<int, array{string, int}>}
     */
    public function match(string $content): array
    {
        $matches = [];
        $offset = 0;
        $width = 0;
        $codeWidth = 0;
        foreach (explode("\n", $content) as $line) {
            if ($width === 0 && $codeWidth > 0) {
                if (preg_match('/^[ \t]*`{' . $codeWidth . ',}[ \t]*$/', $line)) {
                    $codeWidth = 0;
                }
                $offset += strlen($line) + 1;

                continue;
            }
            if ($width === 0 && preg_match('/^[ \t]*(`{3,})(?:[^`\\n]*)$/', $line, $code)) {
                $codeWidth = strlen($code[1]);
                $offset += strlen($line) + 1;

                continue;
            }
            if ($width > 0) {
                if (preg_match('/^[ \t]*(%{3,})[ \t]*$/', $line, $close) && strlen($close[1]) >= $width) {
                    $width = 0;
                } elseif ($line !== '') {
                    $matches[] = [$line, $offset];
                }
            } elseif (preg_match('/^[ \t]*(%{3,})[ \t]*$/', $line, $open)) {
                $width = strlen($open[1]);
            }
            $offset += strlen($line) + 1;
        }

        return $matches === [] ? [] : ['match' => $matches];
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::COMMENT;
    }
}
