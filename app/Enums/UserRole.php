<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case GUEST = 'guest';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }
}
