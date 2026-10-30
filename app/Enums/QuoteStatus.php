<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case New = 'new';
    case Quoted = 'quoted';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
