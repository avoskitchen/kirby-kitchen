<?php

namespace AvosKitchen\Kitchen\Models;

use AvosKitchen\Kitchen\Recipe\Ingredient;
use AvosKitchen\Kitchen\Recipe\IngredientList;
use AvosKitchen\Kitchen\Traits\HasCategoryField;
use Kirby\Cms\Field;
use Kirby\Cms\Page;
use Kirby\Cms\Collection;

class RecipePage extends Page
{
    use HasCategoryField;

    protected static $cuisinesCache;

    public function currentYield(): int
    {
        if (isset($_GET['yield']) && $yield = intval($_GET['yield'])) {
            return $yield;
        }

        return $this->defaultYield();
    }

    public function defaultYield(): int
    {
        if ($yield = $this->yield()->int()) {
            return $yield;
        }
        return 1;
    }

    public function yieldFactor(): float
    {
        return $this->currentYield() / $this->defaultYield();
    }

    public function yieldFormatted(): string
    {
        if ($this->yield()->isEmpty()) {
            return '';
        }

        $singular = (string) $this->yieldSingular();
        $plural = (string) $this->yieldPlural();

        $currentYield = $this->currentYield();

        $ret = '';

        if ($currentYield <= 1 && !empty($singular)) {
            $unit = $singular;
        } else if ($currentYield > 1 && !empty($plural)) {
            $unit = $plural;
        } else {
            $unit = '';
        }

        return snippet('yield', [
            'yield' => $currentYield,
            'unit' => $unit,
            'isDefaultYield' => $this->currentYield() !== $this->defaultYield(),
        ], true);
    }

    public function cuisinesFormatted()
    {
        if (static::$cuisinesCache === null) {
            $cuisines = [];

            foreach ($this->parent()->cuisines()->toStructure() as $item) {
                $cuisines[$item->slug()->value()] = $item->title()->value();
            }

            static::$cuisinesCache = $cuisines;
        }

        $ret = [];

        foreach ($this->content()->cuisines()->split() as $item) {
            $ret[] = static::$cuisinesCache[$item] ?? $item;
        }

        return implode(', ', $ret);
    }

    public function ingredientsFormatted(): Field
    {
        if (!isset($this->cache['kitchen.ingredients'])) {
            $ingredients = $this->content()->ingredients();
            if ($ingredients->isNotEmpty()) {
                $this->cache['kitchen.ingredients'] = new Field($this, 'ingredients', IngredientList::fromString($this, $ingredients)->html($this->yieldFactor()));
            } else {
                $this->cache['kitchen.ingredients'] = $ingredients;
            }
        }

        return $this->cache['kitchen.ingredients'];
    }

    public function instructionsFormatted(): Field
    {
        if (!isset($this->cache['kitchen.instructions'])) {
            $instructions = $this->content()->instructions();
            if ($instructions->isNotEmpty()) {
                $instructions = (new Ingredient($this, null, null, $instructions));
                $instructions = $instructions->format($this->yieldFactor());
                $this->cache['kitchen.instructions'] = (new Field($this, 'instructions', $instructions))->kirbytext();
            } else {
                $this->cache['kitchen.instructions'] = $instructions;
            }
        }
        return $this->cache['kitchen.instructions'];
    }

    public function tipsFormatted(): Field
    {
        if (!isset($this->cache['kitchen.tips'])) {
            $tips = $this->content()->tips();
            if ($tips->isNotEmpty()) {
                $tips = (new Ingredient($this, null, null, $tips))->format($this->yieldFactor());
                $this->cache['kitchen.tips'] = (new Field($this, 'tips', $tips))->kirbytext();
            } else {
                $this->cache['kitchen.tips'] = $tips;
            }
        }
        return $this->cache['kitchen.tips'];
    }

    public function relatedRecipes(bool $unlisted = false): Collection
    {
        $category = $this->category()->value();
        
        if (empty($category)) {
            return new Collection([], $this);
        }
        
        $parent = $this->parent();
        $related = $this->related()->toPages();

        if ($related->count() > 0) {
            $items = $related;
        } else {
            $items = $this->siblings(false)->filterBy('category', $category)->sortBy('lastedited', 'desc');
        }
        
        if ($unlisted === false) {
            $items = $items->listed();
        }

        return $items;
    }

    public function panelListInfo(): string
    {
        $categoryTitle = $this->categoryTitle();

        if ($categoryTitle->isEmpty()) {
            $categoryTitle = '—';
        }

        $styles = [];

        // Cuisines
        $cuisines = $this->cuisinesFormatted();
        $styles[] = "
        @media screen and (min-width: 65em) {
            a[href='{$this->panelUrl()}'] .k-list-item-text::after {
                content: '" . (!empty($cuisines) ? $cuisines : '—') . "';
                font-size: .75rem;
                color: #777;
                width: 33%;
                overflow: hidden;
                display: block;
                text-overflow: ellipsis;
                text-align: right;
            }
        }";

        if (sizeof($styles) !== 0) {
            return $categoryTitle . '<style>' . implode('', $styles) . '</style>';
        } else {
            return $categoryTitle;
        }
    }

    public function panelPopupInfo(): string
    {
        $categoryTitle = $this->categoryTitle();

        if ($categoryTitle->isEmpty()) {
            $categoryTitle = '—';
        }

        $styles = [];
        $elements = [];

        $styles[] = '.k-pages-field.k-field-name-related .k-list-item-text {
            padding-left: 1.75rem;
        }';

        if (sizeof($styles) !== 0) {
            return $categoryTitle . '<style>' . implode('', $styles) . '</style>' . implode('', $elements); // '<style>' . implode('', $styles) . '</style>';
        } else {
            return $categoryTitle;
        }
    }
}
