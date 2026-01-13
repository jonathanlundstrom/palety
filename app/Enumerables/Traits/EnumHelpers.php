<?php namespace App\Enumerables\Traits;

use App\Enumerables\RecipientType;

trait EnumHelpers {
    /**
     * Retrieves a non-backed enum instance by its name.
     *
     * @param string $name The name of the case.
     * @return RecipientType|null Returns the constant value as an instance of the class, or null if the constant is not defined.
     */
    public static function from(string $name): ?self {
        $constant = self::class.'::'.$name;
        return defined($constant)
            ? constant($constant)
            : null;
    }
}
