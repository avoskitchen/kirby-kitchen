<?php

namespace AvosKitchen\Kitchen\Traits;

use AvosKitchen\Kitchen\Category;
use Kirby\Cms\Field;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

/**
 * Used in models for pages that have subpages with a
 * category field.
 */
trait HasCategories
{

    protected static $categoryCache = null;
    protected static $categoriesFieldName = 'categories';

    protected $hasPrivateItems = false;

    /**
     * Returns a list of all defined categories.
     */
    public function getCategories(): array
    {
        if (static::$categoryCache === null) {

            $categories = [];

            foreach ($this->content()->{static::$categoriesFieldName}()->toStructure() as $item) {
                $categories[$item->slug()->value()] = $item->title()->value();
            }

            static::$categoryCache = $categories;
        }

        return static::$categoryCache;
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

        if ($this->hasPrivateItems) {
            // Filter out private items, if user is not logged-in
            $user = $this->kirby()->user();
            if ($user === null) {
                $items = $items->filter(function ($item) {
                    return $item->isPrivate() === false;
                });
            }
        }

        $index = [];

        if (!$items->count() === 0) {
            return new Collection([], $this);
        }

        foreach ($this->getCategories() as $slug => $title) {

            $filtered = $items->filterBy('category', $slug);

            if ($filtered->count() === 0) {
                continue;
            }

            $id = $this->id() . '/' . static::$categoriesFieldName . '/' . $slug;

            $index[] = new Category($id, [
                'slug' => new Field($this, 'slug', $slug),
                'title' => new field($this, 'title', $title),
                'items' => $filtered->sortBy('title', 'asc'),
            ]);
        }

        return new Collection($index, []);
    }
}
