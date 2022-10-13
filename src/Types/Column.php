<?php

namespace Zloter\Cointracking\Types;

enum Column: string
{
    case Time = "Time";
    case Type = "Type";
    case Operation = "Operation";
    case Currency = "Currency";
    case Amount = "Amount";
}