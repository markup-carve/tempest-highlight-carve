<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Tokens;

use Tempest\Highlight\Tokens\TokenType;

/**
 * Verbatim code inside a fence. It contains no other token, so no Carve
 * pattern can color text inside the fence.
 */
enum CarveCodeTokenType: string implements TokenType
{
    case Code = 'carve-code';

    public function getValue(): string
    {
        return $this->value;
    }

    public function canContain(TokenType $other): bool
    {
        return false;
    }
}
