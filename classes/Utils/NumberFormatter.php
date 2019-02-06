<?php

namespace AvosKitchen\Kitchen\Utils;

/**
 * Utility class with some static methods for formatting
 * numbers.
 */
class NumberFormatter
{
    protected static $decimalPoint = ',';
    protected static $decimals  = 1;
    protected static $fractions = [];
    protected static $thousandsSeparator = ',';
    protected static $useFractions = true;

    public static function initSettings(): void
    {
        static::$decimalPoint = option('avoskitchen.kitchen.decimalPoint', '.');
        static::$decimals = option('avoskitchen.kitchen.decimals', 1);
        static::$thousandsSeparator = option('avoskitchen.kitchen.thousandsSeparator', ',');
        static::$useFractions = option('avoskitchen.kitchen.fractions', true);

        static::$fractions = [
            // static::getFractionKey(1 / 6) => '&#8537;',
            // static::getFractionKey(1 / 5) => '&#8533;',
            static::getFractionKey(1 / 4) => '&#188;',
            static::getFractionKey(1 / 3) => '&#8531;',
            // static::getFractionKey(2 / 5) => '&#8534;',
            static::getFractionKey(1 / 2) => '&#189;',
            // static::getFractionKey(3 / 5) => '&#8535;',
            static::getFractionKey(2 / 3) => '&#8532;',
            static::getFractionKey(3 / 4) => '&#190;',
            // static::getFractionKey(4 / 5) => '&#8536;',
            // static::getFractionKey(5 / 6) => '&#8538;',
            // static::getFractionKey(1 / 8) => '&#8539;',
            // static::getFractionKey(3 / 8) => '&#8540;',
            // static::getFractionKey(5 / 8) => '&#8541;',
            // static::getFractionKey(7 / 8) => '&#8542;',

        ];
    }

    protected static function getFractionKey(float $fraction): string
    {
        return rtrim(number_format($fraction, static::$decimals, '.', ''), '0');
    }

    public static function toFraction(float $number): string
    {
        $key = static::getFractionKey($number);

        if (isset(static::$fractions[$key])) {
            return static::$fractions[$key];
        }

        $parts = explode('.', $key);

        if(sizeof($parts) === 2) {
            $decimals = '0.' . $parts[1];

            if (isset(static::$fractions[$decimals])) {
                return $parts[0] . static::$fractions[$decimals];
            }
        }

        return (string) $number;
    }

    protected static function formatValue(float $number): string
    {
        $number = number_format($number, static::$decimals, static::$decimalPoint, static::$thousandsSeparator);
        $number = rtrim(rtrim($number, '0'), static::$decimalPoint); // remove trailing zeroes and comma
        return $number;
    }

    public static function format(float $amount, string $unit = null): string
    {
        if ($unit === 'ml' && $amount >= 1000) {
            $amount /= 1000;
            $unit = 'l';
        } else if ($unit === 'g' && $amount >= 1000) {
            $amount /= 1000;
            $unit = 'kg';
        } else if ($unit === 'kg' && $amount < 1) {
            $amount *= 1000;
            $unit = 'g';
        } else if ($unit === 'TL' && $amount >= 3 && $amount != 4) {
            $amount /= 3;
            $unit = 'EL';
        } else if ($unit === 'EL' && $amount < 1 && $amount !== 0.5) {
            $amount *= 3;
            $unit = 'TL';
        }

        // Convert to fraction if enabled in config.
        if (static::$useFractions === true) {
            $fraction = static::toFraction($amount);
            if ($fraction != (string) $amount) {
                $amount = $fraction;
            } else {
                $amount = static::formatValue($amount);
            }
        } else {
            $amount = static::formatValue($amount);
        }

        if (!empty($unit)) {
            $unit = "\u{00a0}$unit"; // prepend non-breaking space
        }

        return "{$amount}{$unit}";
    }

    public static function formatRange(float $min, float $max, string $unit = null): string
    {
        return static::format($min) . "\u{00a0}–\u{00a0}" . static::format($max, $unit);
    }
}
