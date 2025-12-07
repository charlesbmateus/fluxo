<?php

namespace App\Enums;

enum TransactionType: string
{
    case PAYMENT_IN = 'payment_in';
    case PAYMENT_OUT = 'payment_out';
    case REFUND = 'refund';
    case FEE = 'fee';
    case ADJUSTMENT = 'adjustment';
}
