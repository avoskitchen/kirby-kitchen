<?php

use Kirby\Cms\Collection;

/**
 * Inserts a link to a recipe page into an article.
 * Could/should be extended to display some kind of recipe
 * widget or embed.
 */
return [
    'attr' => [
        'text',
        'class',
        'title',
    ],
    'html' => function ($tag) {

        $parentPage = $tag->parent()->parent();
        $recipesBase = ($parentPage->template()->name() === 'recipes') ? $parentPage->id() : site()->children()->filterBy('template', 'recipes')->first()->id();
        $recipes = array_map('trim', explode(',',$tag->attr('recipes-gallery')));
        $site = site();

        $collection = [];

        foreach ($recipes as $recipe) {

            if (strstr($recipe, '/')) {
                // absolute path
                $target = $recipe;
            } else {
                // just the slug
                $target = "{$recipesBase}/{$recipe}";
            }

            if ($item = $site->find($target)) {
                $collection[] = $item;
            }
        }

        return snippet('recipes-gallery', [
            'recipes' => new Collection($collection),
        ], true);
    },
];
