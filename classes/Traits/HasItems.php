<?php

namespace AvosKitchen\Kitchen\Traits;

use Kirby\Toolkit\Collection;

trait HasItems
{
    public function children(): Collection
    {
        $items = parent::children();

        // Filter out private items, if user is not logged-in
        if ($this->hasPrivateItems() && $this->kirby()->user() === null) {
            $items = $items->filter(function ($item) {
                return $item->isPrivate() === false;
            });
        }

        return $items;
    }
}
