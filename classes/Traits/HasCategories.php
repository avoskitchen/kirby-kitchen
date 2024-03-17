<?php

namespace AvosKitchen\Kitchen\Traits;

use AvosKitchen\Kitchen\Category;
use Kirby\Content\Field;
use Kirby\Toolkit\Collection;

/**
 * Used in models for pages that have subpages with a
 * category field.
 */
trait HasCategories
{
    protected static $allCategoryCache = null;
    protected static $nonEmptyCategoryCache = null;

    /**
     * Returns a list of all defined categories.
     */
    public function getCategories(bool $empty = true, bool $unlisted = false): array
    {
        if (static::$allCategoryCache === null) {
            $categories = [];

            foreach ($this->content()->get('categories')->toStructure() as $item) {
                $categories[$item->slug()->value()] = $item->title()->value();
            }

            static::$allCategoryCache = $categories;
        }

        if ($empty === true) {
            return static::$allCategoryCache;
        }

        if (static::$nonEmptyCategoryCache === null) {
            $items = $this->children();

            if ($unlisted === false) {
                $items = $items->listed();
            }

            $keysArray = $items->pluck('category', null, true);
            $keysArray = array_flip(array_map(function ($v) {
                return $v->value();
            }, $keysArray));

            static::$nonEmptyCategoryCache = array_intersect_key(static::$allCategoryCache, $keysArray);
        }

        return static::$nonEmptyCategoryCache;
    }

    /**
     * Returns a Collection object holding all categories
     * and their respective items.
     */
    public function getItemsGroupedByCategory(bool $unlisted = false): Collection
    {
        $items = $this->children();

        if ($unlisted === false) {
            $items = $items->listed();
        }

        $index = [];

        if (! $items->count() === 0) {
            return new Collection([]);
        }

        foreach ($this->getCategories() as $slug => $title) {
            $filtered = $items->filterBy('category', $slug);

            if ($filtered->count() === 0) {
                continue;
            }

            $id = $this->id() . '/categories/' . $slug;

            $index[] = new Category($id, [
                'slug' => new Field($this, 'slug', $slug),
                'title' => new field($this, 'title', $title),
                'items' => $filtered->sortBy('title', 'asc'),
            ]);
        }

        return new Collection($index);
    }
}
