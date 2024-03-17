<?php

namespace AvosKitchen\Kitchen\Models;

use AvosKitchen\Kitchen\Traits\HasCategories;
use AvosKitchen\Kitchen\Traits\HasUnits;
use Kirby\Cms\Page;
use Kirby\Toolkit\Collection;

class RecipesPage extends Page
{
    use HasCategories;
    use HasUnits;

    public function latest(int $limit = 0, bool $unlisted = false): Collection
    {
        $items = $this->children();

        if ($unlisted === false) {
            $items = $items->listed();
        }

        $items = $items->sortBy('created', 'desc');

        if ($limit > 0) {
            $items = $items->limit($limit);
        }

        return $items;
    }
}
