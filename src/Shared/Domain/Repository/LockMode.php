<?php

namespace App\Shared\Domain\Repository;

enum LockMode: int
{
    case NONE = 0;
    case PESSIMISTIC_SHARED = 1;
    case PESSIMISTIC_EXCLUSIVE = 2;
    case OPTIMISTIC = 4;
}