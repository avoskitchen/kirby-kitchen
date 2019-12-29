<?php

namespace AvosKitchen\Kitchen\Models;

use AvosKitchen\Kitchen\Traits\HasCategories;
use AvosKitchen\Kitchen\Traits\HasUnits;
use Kirby\Cms\Page;
use Kirby\Cms\Field;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

class RecipesPage extends Page
{
    use HasCategories;
    use HasUnits;

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public function latest(int $limit = 0, bool $unlisted = false): Collection {
        
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
