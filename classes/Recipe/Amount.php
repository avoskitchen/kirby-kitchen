<?php

namespace AvosKitchen\Kitchen\Recipe;

use AvosKitchen\Kitchen\Utils\Chars;
use AvosKitchen\Kitchen\Utils\NumberFormatter;
use Kirby\Cms\Page;

/**
 * Hold information about an amount of a cooking ingredient,
 * usually consisting of a number or a word, that roughly
 * descripes the amount of the specific ingredient.
 */
class Amount
{
    const TYPE_FLOAT = 1;
    const TYPE_RANGE = 2;
    const TYPE_FRACTION = 4;
    const TYPE_UNKNOWN = 8;
    const FALLBACK_AMOUNT = 2;

    protected $page;

    protected $type;
    protected $prefix;
    protected $min;
    protected $max;
    protected $unit;

    const REGEX_PREFIXES = 'ca\.|~'; // prefix of an amount

    public function __construct(Page $page, string $amount = null, string $unit = null)
    {
        $this->page = $page;

        $amount = trim($amount);

        if (preg_match('/^((?:' . Chars::REGEX_PREFIXES . ')\s?)(.*)$/', $amount, $matches)) {
            // Extract amount prefix
            $this->prefix = trim($matches[1]);
            $amount = $matches[2];
        }

        if (preg_match('/^([\d.,])+([' . Chars::REGEX_SPACES . ']*[-–—][' . Chars::REGEX_SPACES . ']*)([\d.,])+$/', $amount, $matches)) {
            // Range, e.g. 2 - 6
            list(, $min, $sep, $max) = $matches;
            $this->min = (float) $min;
            $this->max = (float) $max;
            $this->type = static::TYPE_RANGE;
        } else if (preg_match('/^([\d+,.])\/([\d+,.]+)$/', $amount, $matches)) {
            // Fraction, e.g. 1/2
            list(, $nom, $dnom) = $matches;
            $this->min = $this->max = (float) ($nom / $dnom);
            $this->type = static::TYPE_FRACTION;
        } else if ((float) $amount > 0) {
            // Simple numeric value, e.g. 12 or 1,5
            $this->min = $this->max = (float) $amount;
            $this->type = static::TYPE_FLOAT;
        } else {
            // Unknown value
            $this->min = $this->max = $amount;
            $this->type = static::TYPE_UNKNOWN;
        }

        $this->unit = !empty($unit) ? $unit : null;
    }

    public function format(float $yieldFactor = 1): string
    {
        $prefix = $this->prefix !== null ? "{$this->prefix} " : '';

        switch ($this->type) {
            case static::TYPE_UNKNOWN:
                // Non-numeric amounts (e.g. some leaves)
                $unit = $this->unit !== null ? $this->page->parent()->unitNumerus($this->unit, static::FALLBACK_AMOUNT) : '';
                return trim($prefix . $this->min . ' ' . $unit, Chars::SPACES);

            case static::TYPE_RANGE:
                // Range (e.g. 10 - 20 g)
                return trim($prefix . NumberFormatter::formatRange($yieldFactor * $this->min, $yieldFactor * $this->max, $this->unit), Chars::SPACES);

            default:
                // Single numeric amount (10 g)
                return trim($prefix . NumberFormatter::format($yieldFactor * $this->min, $this->unit));
        }
    }

    public function isNumeric(): bool
    {
        return $this->type !== static::TYPE_UNKNOWN;
    }

    public function toFloat(float $yieldFactor = 1): float
    {
        return $yieldFactor * $this->max;
    }
}
