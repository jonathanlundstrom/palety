<?php namespace App\Enumerables\Interfaces;

/**
 * The purpose of this interface is to make sure each enum has
 * a proper implementation of the color method used in tables.
 */
interface ColoredEnum {
    /**
     * Returns the color associated with the current enum value.
     * Response value should be base on the enum name or default.
     *
     * @return string
     */
    public function color(): string;
}
