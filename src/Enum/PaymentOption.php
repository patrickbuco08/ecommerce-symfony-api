<?php

namespace Bocum\Enum;

enum PaymentOption: string
{
    case CASH = 'cash';
    case GCASH = 'gcash';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
}
