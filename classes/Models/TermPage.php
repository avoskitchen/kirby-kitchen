<?php

namespace AvosKitchen\Kitchen\Models;

use AvosKitchen\Kitchen\Traits\HasCategoryField;
use Kirby\Cms\Page;

class TermPage extends Page
{
    use HasCategoryField;

    public function panelListInfo(): string
    {
        $categoryTitle = $this->categoryTitle();

        if ($categoryTitle->isEmpty()) {
            return '—';
        }

        return $categoryTitle;
    }
}
