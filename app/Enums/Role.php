<?php

namespace App\Enums;

enum Role: string
{
    case CLIENT = 'client';
    case PROVIDER = 'provider';
    case ADMIN = 'admin';
}
