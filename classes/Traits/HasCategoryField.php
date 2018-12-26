<?php

namespace AvosKitchen\Kitchen\Traits;

use Kirby\Cms\Field;
use Kirby\Toolkit\Obj;

/**
 * Used in pages where the parent page has category
 * definitions (i.e. its model uses the HasCategoriesField
 * trait).
 */
trait HasCategoryField
{
    protected static $categoryFieldName = 'category';

    public function categoryTitle(): Field
    {
        $parent   = $this->parent();
        $category = $this->{static::$categoryFieldName}()->value();

        $title = null;

        if (!empty($category)) {
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
