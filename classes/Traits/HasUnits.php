<?php

namespace AvosKitchen\Kitchen\Traits;

use Kirby\Cms\Structure;

/**
 * Used by pages that hold information of units used in
 * ingredient lists.
 */
trait HasUnits
{
    protected static $unitsCache;
    protected static $unitValuesCache;
    protected static $unitsSingularCache;
    protected static $unitsPluralCache;

    protected static $unitsFieldName = 'units';

    public function getUnits(): Structure
    {
        if (static::$unitsCache === null) {
            static::$unitsCache = $this->{static::$unitsFieldName}()->toStructure();
        }

        return static::$unitsCache;
    }

    /**
     * Returns given amount with the corresponding singular
     * or plural form of the corresponding unit.
     */
    public function unitNumerus(string $unit, float $amount): string
    {
        if (is_null(static::$unitsSingularCache) || is_null(static::$unitsPluralCache)) {
            $unitsSingular = [];
            $unitsPlural = [];

            foreach ($this->getUnits() as $item) {
                $singular = $item->singular()->value();
                $plural = $item->plural()->value();

                if (! empty($singular) && ! empty($unitsPlural)) {
                    $unitsSingular[$singular] = $plural;
                    $unitsPlural[$plural] = $singular;
                }
            }

            static::$unitsSingularCache = $unitsSingular;
            static::$unitsPluralCache = $unitsPlural;
        }

        if (isset(static::$unitsSingularCache[$unit]) && $amount !== 1) {
            return static::$unitsSingularCache[$unit];
        } elseif (isset(static::$unitsPluralCache[$unit]) && $amount === 1) {
            return static::$unitsPluralCache[$unit];
        }

        return $unit;
    }

    /**
     * Returns an array of all defined units, including
     * their abbreviation, singular and plural forms.
     */
    public function unitValues(): array
    {
        if (is_null(static::$unitValuesCache)) {
            $units = [];

            foreach ($this->getUnits() as $item) {
                $singular = $item->singular()->value();
                $short = $item->short()->value();
                $plural = $item->plural()->value();

                if (! empty($short)) {
                    $units[] = $short;
                }

                if (! empty($plural)) {
                    $units[] = $plural;
                }

                $units[] = $singular;
            }

            static::$unitValuesCache = $units;
        }

        return static::$unitValuesCache;
    }
}
