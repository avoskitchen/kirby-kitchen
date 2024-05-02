<?php

namespace AvosKitchen\Kitchen\Recipe;

use AvosKitchen\Kitchen\Utils\Chars;
use Kirby\Cms\Page;

class Ingredient
{
    protected $amount;
    protected $unit;
    protected $item;
    protected $page;

    public static $unitsPattern = '';
    public static $units = '';

    public function __construct(Page $page, $amount = null, string $unit = null, string $item = '')
    {
        static::initPatterns($page);

        $this->page = $page;
        $this->amount = new Amount($page, $amount, $unit);
        $this->item = $this->parseItem($item);
    }

    public static function fromString(Page $page, string $ingredient): Ingredient
    {
        static::initPatterns($page);

        preg_match(
            '/^
            (?:-\s)? # Optional list item\/ingredient indicator prefix
            ((?:(?:(?:~|ca.)\s*)?[½⅓⅔¼¾\d,.][½⅓⅔¼¾\d,.\-–—\/' . Chars::REGEX_SPACES . ']*))? # Prefix + Amount
            (?:((?:' . static::$unitsPattern . ')[\?\!]?))? # Unit
            (?:\s+(.*)?) # Item description (rest of line)
            $/ux',
            trim($ingredient ?? ''),
            $matches
        );

        if (sizeof($matches) > 0) {
            list(, $amount, $unit, $item) = $matches;

            if (empty($unit) && ! empty($item) && isset(static::$units[$item])) {
                // If unit was empty, but not item and item
                // matches a unit, use item as unit.
                $unit = $item;
                $item = null;
            }

            return new static($page, $amount, (string) $unit, (string) $item);
        } else {
            $ingredient = preg_replace('/^[-*+]\s+/', '', $ingredient);

            return new static($page, null, null, $ingredient);
        }
    }

    protected function parseItem($text): array
    {
        $tokens = preg_split('/({{[^}]+}})/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $item = [];

        foreach ($tokens as $token) {
            if (substr($token, 0, 2) === '{{') {
                // Create a sub-item
                $token = trim($token, '{}' . Chars::SPACES);
                $item[] = static::fromString($this->page, $token);
            } else {
                // Parse item for numeri (singular/plural forms)
                while (($start = strpos($token, '[')) !== false) {
                    if (isset($token[$start + 1]) && $token[$start + 1] === '[') {
                        // Skip double square brackets, e.g. `[[wiki-link]]`
                        $item[] = substr($token, 0, $start + 2);
                        $token = substr($token, $start + 2);

                        continue;
                    }

                    $or = strpos($token, '|', $start);
                    $end = strpos($token, ']', $start);

                    if ($or !== false && $end !== false && $end > $or) {
                        $item[] = substr($token, 0, $start);

                        $numeri = explode('|', substr($token, $start + 1, $end - $start - 1));

                        $singular = $numeri[0];
                        $plural = $numeri[1];

                        $item[] = new Numerus($singular, $plural);
                        $token = substr($token, $end + 1);
                    } else {
                        $item[] = substr($token, 0, $start + 1);
                        $token = substr($token, $start + 1);
                    }
                }

                $item[] = $token;
            }
        }

        return sizeof($item) > 0 ? $item : null;
    }

    public function format(float $yieldFactor = 1, string $template = '{amount} {item}'): string
    {
        if (! is_null($this->amount)) {
            $amount = $this->amount->format($yieldFactor);
        } else {
            $amount = '';
        }

        $item = [];

        foreach ($this->item as $part) {
            if ($part instanceof Ingredient) {
                $item[] = rtrim($part->format($yieldFactor, "{amount}\u{00a0}{item}"), "\u{00a0}"); // trim no break space it $item was empty
            } elseif ($part instanceof Numerus) {
                $item[] = $part->resolve($this->amount->isNumeric() ? $this->amount->toFloat($yieldFactor) : 2);
            } else {
                $item[] = $part;
            }
        }

        $item = trim(implode('', $item) ?? '');

        return trim(static::strTemplate($template, compact('amount', 'item')));
    }

    public function html(float $yieldFactor = 1): string
    {
        $template = '<li class="' . option('avoskitchen.kitchen.ingredientClass', 'ingredient') . '" markdown="1"><span class="' . option('avoskitchen.kitchen.ingredientAmountClass') . '"><span>{amount}&nbsp;</span></span><span class="' . option('avoskitchen.kitchen.ingredientItemClass') . '"><span>{item}</span></span></li>';

        return $this->format($yieldFactor, $template);
    }

    public function __toString(): string
    {
        return $this->format();
    }

    protected static function initPatterns(Page $page): void
    {
        if (empty(static::$unitsPattern)) {
            $units = $page->parent()->unitValues();
            usort($units, function ($a, $b) {
                // Sort by length
                return strlen($b) <=> strlen($a);
            });
            $units = array_map(function ($var) {
                return preg_quote($var, '/');
            }, $units);
            static::$units = array_flip($units);
            static::$unitsPattern = implode('|', $units);
        }
    }

    /**
     * A super simple string template engine,
     * which replaces tags like {mytag} with any other string
     *
     * @param  string $string
     * @param  array  $data An associative array with keys, which should be replaced and values.
     * @return string
     */
    public static function strTemplate($string, $data = []): string
    {
        $replace = [];
        foreach ($data as $key => $value) {
            $replace['{' . $key . '}'] = $value;
        }

        return str_replace(array_keys($replace), array_values($replace), $string);
    }

    public function __debugInfo()
    {
        return [
            'amount' => $this->amount,
            'unit' => $this->unit,
            'item' => $this->item,
            'page' => $this->page->id(),
        ];
    }
}
