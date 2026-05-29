<?php

namespace App\Enums;

enum MatchStrategy: string
{
    case Exact = 'exact';
    case CaseInsensitive = 'case_insensitive';
    case Contains = 'contains';
}
