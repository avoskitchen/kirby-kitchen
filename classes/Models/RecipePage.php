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

    public function isPrivate(): bool
    {
        return (option('avoskitchen.kitchen.privateRecipes') && $this->private()->bool());
    }

    public function userHasAccess(): bool
    {
        if (!option('avoskitchen.kitchen.privateRecipes') || !$this->private()->bool()) {
            // Do no further checks, if the private recipes feature is disabled
            // or the recipe is not private.
            return true;
        }

        // Recipe is private, so return true if a looged-in
        // user is given, otherwise false.
        return kirby()->user() !== null;
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
            $items = $this->siblings(false)->filterBy('category', $category)->sortBy('title', 'asc');
        }
        
        if ($unlisted === false) {
            $items = $items->listed();
        }

        if ($parent->hasPrivateItems()) {
            // Filter out private items, if user is not logged-in
            $user = $this->kirby()->user();
            if ($user === null) {
                $items = $items->filter(function ($item) {
                    return $item->isPrivate() === false;
                });
            }
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

        // Private recipes
        if ($this->isPrivate()) {
            $color = 'rgb(22, 23, 26);';
            $icon = 'url(\'data:image/svg+xml;base64,' . base64_encode('<svg viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg"><title>lock</title><g fill="' . $color . '"><path fill="' . $color . '" d="M8,0C5.8,0,4,1.8,4,4v1H2C1.4,5,1,5.4,1,6v9c0,0.6,0.4,1,1,1h12c0.6,0,1-0.4,1-1V6c0-0.6-0.4-1-1-1h-2V4 C12,1.8,10.2,0,8,0z M9,11.7V13H7v-1.3c-0.6-0.3-1-1-1-1.7c0-1.1,0.9-2,2-2s2,0.9,2,2C10,10.7,9.6,11.4,9,11.7z M10,5H6V4 c0-1.1,0.9-2,2-2s2,0.9,2,2V5z"></path></g></svg>') . '\')';

            $styles[] = "a[href='{$this->panelUrl()}'] .k-list-item-text em::after {
                content: '';
                display: inline-block;
                height: .75rem;
                width: .75rem;
                margin-left: .75ch;
                position: relative;
                background: {$icon} 50% 50% no-repeat;
            }";
        }

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

        // Private recipes
        if ($this->isPrivate()) {
            $color = 'rgb(22, 23, 26);';
            
            $elements[] = '<svg viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg"><title>lock</title><g fill="' . $color . '"><path fill="' . $color . '" d="M8,0C5.8,0,4,1.8,4,4v1H2C1.4,5,1,5.4,1,6v9c0,0.6,0.4,1,1,1h12c0.6,0,1-0.4,1-1V6c0-0.6-0.4-1-1-1h-2V4 C12,1.8,10.2,0,8,0z M9,11.7V13H7v-1.3c-0.6-0.3-1-1-1-1.7c0-1.1,0.9-2,2-2s2,0.9,2,2C10,10.7,9.6,11.4,9,11.7z M10,5H6V4 c0-1.1,0.9-2,2-2s2,0.9,2,2V5z"></path></g></svg>';

            $styles[] = ".k-pages-field.k-field-name-related .k-list-item-text small svg {
                position: absolute;
                left: 46px;
                height: .75rem;
                width: .75rem;
                top: 12px;
            }";
        }

        if (sizeof($styles) !== 0) {
            return $categoryTitle . '<style>' . implode('', $styles) . '</style>' . implode('', $elements); // '<style>' . implode('', $styles) . '</style>';
        } else {
            return $categoryTitle;
        }
    }
}
