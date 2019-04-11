<?php

namespace AvosKitchen\Kitchen\Traits;

use Kirby\Toolkit\Collection;

trait HasItems
{
    public function getItems(bool $unlisted = false): Collection
    {
        $items = $this->children();

        if ($unlisted === false) {
            $items = $items->listed();
        }

        // Filter out private items, if user is not logged-in
        if ($this->hasPrivateItems() && $this->kirby()->user() === null) {
            $items = $items->filter(function ($item) {
                return $item->isPrivate() === false;
            });
        }

        return $items;
    }
}