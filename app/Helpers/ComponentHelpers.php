<?php

namespace App\Helpers;

use BackedEnum;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use UnitEnum;

trait ComponentHelpers {
    /**
     * Fields that hydrateFields() should skip, either because they must never be
     * pre-filled or because the component hydrates them itself.
     *
     * @return array<int, string>
     */
    protected function skipHydration(): array {
        return ['password'];
    }

    /**
     * Populates the properties of the current object based on a given object's attributes.
     *
     * Iterates over the attributes of the current object, checking if they are instances
     * of the `Validate` class. For each matching attribute, retrieves a related property
     * from the provided object. If the property exists and is not null, assigns its value
     * to the corresponding property of the current object. If the value is an instance of
     * `UnitEnum`, assigns the name of the enumeration instead.
     *
     * @param  object  $object  The source object from which to populate the current object's properties.
     */
    protected function hydrateFields(object $object): void {
        $fields = get_object_vars($this)['attributes'];
        foreach ($fields as $field) {
            if (get_class($field) === Validate::class) {
                $property_name = $field->getSubName();
                if (in_array($property_name, $this->skipHydration(), true)) {
                    continue;
                }

                if (! is_null($object->{$property_name})) {
                    if ($object->{$property_name} instanceof BackedEnum) {
                        $this->{$property_name} = $object->{$property_name}->value;
                    } elseif ($object->{$property_name} instanceof UnitEnum) {
                        $this->{$property_name} = $object->{$property_name}->name;
                    } elseif ($object->{$property_name} instanceof Collection) {
                        $this->{$property_name} = $object->{$property_name}
                            ->pluck('id')
                            ->toArray();
                    } else {
                        $this->{$property_name} = $object->{$property_name};
                    }
                }
            }
        }
    }
}
