<?php

namespace AvosKitchen\Kitchen\Traits;

use Kirby\Cms\Field;

/**
 * Used in pages where the parent page has category
 * definitions (i.e. its model uses the HasCategoriesField
 * trait).
 */
trait HasCategoryField
{
    public function categoryTitle(): Field
    {
        $parent = $this->parent();
        $category = $this->content()->get('category')->value();

        $title = null;

        if (! empty($category)) {
            $categories = $parent->getCategories();

            if (isset($categories[$category])) {
                $title = $categories[$category];
            } else {
                $title = $category;
            }
        }

        return new Field($this, 'categoryTitle', $title);
    }
}
