<?php

namespace AvosKitchen\Kitchen\Recipe;

/**
 * Takes the singular and plural form of a noun and and can
 * resolve them based on a given amount. Only supports
 * languages with singular form (n = 1) and plural form (n != 1)
 * or languages with no numeri at all.
 *
 * Languages with duals, paucals (and possibly other numeri
 * are not fully supported yet).
 */
class Numerus
{
    protected $singular;
    protected $plural;

    public function __construct(string $singular, string $plural)
    {
        $this->singular = $singular;
        $this->plural = $plural;
    }

    public function resolve($amount): string
    {
        return ($amount >= -1 && $amount <= 1) ? $this->singular : $this->plural;
    }
}
