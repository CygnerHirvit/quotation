<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case StudioAdmin = 'studio_admin';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::StudioAdmin => 'Studio Admin',
            self::Staff => 'Staff',
        };
    }
}
