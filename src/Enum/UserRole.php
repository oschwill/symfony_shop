<?php
// src/Enum/UserRole.php
namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}