<?php

namespace AvosKitchen\Kitchen\Utils;

/**
 * utility class with some static methods for formatting numbers.
 */
class NumberFormatter
{
    protected static $instance;

    protected $decimalPoint = ',';
    protected $decimals = 2;
    protected $fractions = [];
    protected $thousandsSeparator = ',';
    protected $useFractions = true;
    protected $isInit = false;

    public $fractionEquivalents = [
        '½' => 1 / 2,
        '⅓' => 1 / 3,
        '⅔' => 2 / 3,
        '¼' => 1 / 4,
        '¾' => 3 / 4,
    ];

    protected function __construct()
    {
        $this->decimalPoint = option('avoskitchen.kitchen.decimalPoint', '.');
        $this->decimals = option('avoskitchen.kitchen.decimals', 2);
        $this->thousandsSeparator = option('avoskitchen.kitchen.thousandsSeparator', ',');
        $this->useFractions = option('avoskitchen.kitchen.fractions', true);

        $this->fractions = [
            $this->getFractionKey(1 / 4) => '&#188;',
            $this->getFractionKey(1 / 3) => '&#8531;',
            $this->getFractionKey(1 / 2) => '&#189;',
            $this->getFractionKey(2 / 3) => '&#8532;',
            $this->getFractionKey(3 / 4) => '&#190;',
        ];
    }

    public static function instance(): NumberFormatter
    {
        return static::$instance ?? (static::$instance = new static());
    }

    protected function getFractionKey(float $fraction): string
    {
        return rtrim(number_format($fraction, $this->decimals, '.', ''), '0');
    }

    public function toFraction(float $number): string
    {
        $key = $this->getFractionKey($number);

        if (isset($this->fractions[$key])) {
            return $this->fractions[$key];
        }

        $parts = explode('.', $key);

        if (sizeof($parts) === 2) {
            $decimals = '0.' . $parts[1];

            if (isset($this->fractions[$decimals])) {
                return $parts[0] . $this->fractions[$decimals];
            }
        }

        return (string) $number;
    }

    protected function formatValue(float $number): string
    {
        $number = number_format($number, $this->decimals, $this->decimalPoint, $this->thousandsSeparator);
        $number = rtrim(rtrim($number, '0'), $this->decimalPoint); // remove trailing zeroes and comma

        return $number;
    }

    public function format(float $amount, string $unit = null): string
    {
        $isFraction = in_array($amount, [0.25, 0.5, 0.75], true);

        if ($unit === 'ml' && $amount >= 1000) {
            $amount /= 1000;
            $unit = 'l';
        } elseif ($unit === 'g' && $amount >= 1000) {
            $amount /= 1000;
            $unit = 'kg';
        } elseif ($unit === 'kg' && $amount < 1) {
            $amount *= 1000;
            $unit = 'g';
        } elseif ($unit === 'TL' && $amount >= 3 && $amount !== 4) {
            $amount /= 3;
            $unit = 'EL';
        } elseif ($unit === 'EL' && $amount < 1 && $isFraction === false) {
            $amount *= 3;
            $unit = 'TL';
        }

        if (in_array($unit, ['g', 'ml']) === true) {
            // no need for decimal for small units
            $amount = round($amount, 0);
        }

        if (in_array($unit, ['EL', 'TL', 'Prise', 'Prisen']) === true && $isFraction === false) {
            // no need for decimal for small units
            $amount = round($amount, 1);
        }

        // Smooth out values
        $floor = floor($amount);
        $decimals = $amount - $floor;
        foreach ([.25, .5, .75, 1] as $frac) {
            if (abs(($decimals - $frac) / $frac) < .125) {
                $amount = $floor + $frac;

                break;
            }
        }

        // convert to fraction if enabled in config
        if ($this->useFractions === true) {
            $fraction = $this->toFraction($amount);
            if ($fraction !== (string) $amount) {
                $amount = $fraction;
            } else {
                $amount = $this->formatValue($amount);
            }
        } else {
            $amount = $this->formatValue($amount);
        }

        if (! empty($unit)) {
            $unit = "\u{00a0}$unit"; // prepend non-breaking space
        }

        return "{$amount}{$unit}";
    }

    public function formatRange(float $min, float $max, string $unit = null): string
    {
        return $this->format($min) . "\u{00a0}<span class=\"endash\">–</span>\u{00a0}" . $this->format($max, $unit);
    }

    public function fractionToFloat(string $fraction): float
    {
        if (isset($this->fractionEquivalents[$fraction])) {
            return $this->fractionEquivalents[$fraction];
        }

        $parts = explode('/', $fraction);
        if (sizeof($parts) > 0) {
            return (float) ((int) $parts[0] / (int) $parts[0]);
        }

        return null;
    }
}
