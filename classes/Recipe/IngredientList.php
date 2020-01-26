<?php

namespace AvosKitchen\Kitchen\Recipe;

use Kirby\Cms\Page;

/**
 * A list of ingredients for cooking recipes, consisting of
 * text ingredient entries, text and headline blocks.
 */

class IngredientList
{
    const ITEM_INGREDIENT = 1;
    const ITEM_TEXT       = 2;
    const ITEM_HEADLINE   = 3;

    public $items = [];

    protected $page;
    protected $defaultYield;
    protected $yield;
    protected $yieldFactor;

    protected function __construct(Page $page, int $defaultYield = 1, int $yield = 1, array $items = null)
    {
        $this->page         = $page;
        $this->defaultYield = $defaultYield;
        $this->yield        = $yield;

        $this->yieldFactor  = $this->defaultYield / $this->yield;
        $this->items        = $items;
    }

    public static function fromString(Page $page, string $text): IngredientList
    {
        $ingredients = [];
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        foreach (explode("\n", $text) as $i => $ingredient) {
            if (strlen($ingredient) > 0 && $ingredient[0] === '-') {
                $ingredients[] = Ingredient::fromString($page, $ingredient);
            } else {
                $ingredients[] = $ingredient;
            }
        }

        return new static($page, 1, 1, $ingredients);
    }

    public function html(float $yieldFactor = 1): string
    {
        if (sizeof($this->items) === 0) {
            return '';
        }

        $html = [];

        $html[] = '<div class="ingredient-group" markdown="1">';
        
        $lastItem = null;

        foreach ($this->items as $item) {
            
            if ($item instanceof Ingredient) {
                if ($lastItem !== static::ITEM_INGREDIENT) {
                    $html[] = '<ul markdown="1">';
                }
                $html[] = $item->html($yieldFactor);
                $lastItem = static::ITEM_INGREDIENT;
            } else {
                if ($lastItem === static::ITEM_INGREDIENT) {
                    $html[] = '</ul>';
                }
                
                if ($lastItem !== null && strlen($item) > 0 && $item[0] === '#') {
                    $html[] = '</div>';
                    $html[] = '<div class="ingredient-group" markdown="1">';
                }

                $html[] = (new Ingredient($this->page, null, null, $item))->format($yieldFactor);
                
                $lastItem = 'text';
            }
        }

        if($lastItem === static::ITEM_INGREDIENT) {
            $html[] = '</ul>';
        }

        $html[] = '</div>';

        $html = implode("\n", $html);

        // Parse Kirbytext, so the <p> tag fix can be added after
        $html = kirbytext($html, [
            'parent' => $this->page
        ]);

        // Remove <p> tags that are added by Parsedown to list elements as soon
        // as they contain spans.
        $html = preg_replace('/(<li class="ingredient">)\s*<p>(.*)<\/p>\s*(<\/li>)/siU', '$1$2$3', $html);

        return $html;

    }
}
