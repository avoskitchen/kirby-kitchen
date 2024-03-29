<?php

namespace AvosKitchen\Kitchen\Models;

use AvosKitchen\Kitchen\Recipe\Ingredient;
use AvosKitchen\Kitchen\Recipe\IngredientList;
use AvosKitchen\Kitchen\Traits\HasCategoryField;
use Kirby\Cms\Collection;
use Kirby\Content\Field;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

class RecipePage extends Page
{
    use HasCategoryField;

    public const DIAMETER_DEFAULT = 26; // cm
    public const DIAMETER_MIN = 10; // cm
    public const DIAMETER_MAX = 50; // cm

    protected static $cuisinesCache;
    protected $typeCache;

    protected array $cache = [];

    public function currentYield(): int
    {
        if ($yield = (int) get('yield')) {
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

    public function currentDiameter(): ?int
    {
        if ($this->isType('pie') === true) {
            if ($diameter = (int) get('diameter')) {
                return min($this->maxDiameter(), max($this->minDiameter(), $diameter));
            }

            return $this->defaultDiameter();
        }

        return null;
    }

    public function minDiameter(): ?int
    {
        if ($this->isType('pie') === true) {
            return max(static::DIAMETER_MIN, $this->diameterMin()->or(static::DIAMETER_MIN)->toInt());
        }

        return null;
    }

    public function maxDiameter(): ?int
    {
        if ($this->isType('pie') === true) {
            return min(static::DIAMETER_MAX, $this->diameterMax()->or(static::DIAMETER_MAX)->toInt());
        }

        return null;
    }

    public function defaultDiameter(): ?int
    {
        if ($this->isType('pie') === true) {
            return $this->diameter()->or(static::DIAMETER_DEFAULT)->toInt();
        }

        return null;
    }

    public function isType(string $type = ''): bool
    {
        if ($this->typeCache === null) {
            $this->typeCache = $this->content()->get('type')->toString();
        }

        return $this->typeCache === $type;
    }

    public function yieldFactor(): float
    {
        $yieldFactor = $this->currentYield() / $this->defaultYield();

        if ($this->isType('pie') === true) {
            $areaDefault = pow($this->defaultDiameter() / 2, 2) * pi();
            $areaConverted = pow($this->currentDiameter() / 2, 2) * pi();
            $areaFactor = $areaConverted / $areaDefault;

            return $yieldFactor * $areaFactor;
        }

        return $yieldFactor;
    }

    public function yieldFormatted(string $output = 'html'): string
    {
        if ($this->yield()->isEmpty()) {
            return '';
        }

        $singular = (string) $this->yieldSingular();
        $plural = (string) $this->yieldPlural();

        $currentYield = $this->currentYield();

        $ret = '';

        if ($currentYield <= 1 && ! empty($singular)) {
            $unit = $singular;
        } elseif ($currentYield > 1 && ! empty($plural)) {
            $unit = $plural;
        } else {
            $unit = '';
        }

        $data = [
            'yield' => $currentYield,
            'unit' => $unit,
            'isDefaultYield' => $this->currentYield() !== $this->defaultYield(),
        ];

        if ($output === 'text') {
            return Str::template('{{ yield }} {{ unit }}', $data);
        } else {
            return snippet('yield', $data, true);
        }
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
        if (! isset($this->cache['kitchen.ingredients'])) {
            $ingredients = $this->content()->ingredients();
            if ($ingredients->isNotEmpty()) {
                $this->cache['kitchen.ingredients'] = new Field($this, 'ingredients', IngredientList::fromString($this, $ingredients)->html($this->yieldFactor()));
            } else {
                $this->cache['kitchen.ingredients'] = $ingredients;
            }
        }

        return $this->cache['kitchen.ingredients'];
    }

    public function ingredientsArray(): array
    {
        $ingredients = $this->content()->ingredients()->toString();
        $ingredients = preg_replace('/<\!--(.*)-->/sU', '', $ingredients);

        return IngredientList::fromString($this, $ingredients)->toArray($this->yieldFactor());
    }

    public function instructionsFormatted(): Field
    {
        if (! isset($this->cache['kitchen.instructions'])) {
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
        if (! isset($this->cache['kitchen.tips'])) {
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
        return $this->categoryTitle()->or('—');
    }
}
