<?php

namespace Zloter\Cointracking\Types;

enum TransactionType: string
{
    case TRADE = "Trade";
    case REWARD = "Reward/Bonus";
    case FEE = "Other fee";
    case DEPOSIT = "Deposit";
    case MINING = "Mining";
}