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

        $site = $tag->site();
        $user = $tag->kirby()->user();
        $parentPage = $tag->parent()->parent();

        $recipesBase = ($parentPage->template()->name() === 'recipes')
            ? $parentPage
            : $site->children()->filterBy('template', 'recipes')->first();

        $recipes = array_map('trim', explode(',', $tag->attr('recipes-gallery')));
        $collection = [];

        foreach ($recipes as $recipe) {
            $item = null;

            if (strstr($recipe, '/') === true) {
                // absolute path
                $item = $site->findPageOrDraft($recipe);
            } else {
                // just the slug
                $item = $recipesBase->findPageOrDraft($recipe);
            }

            if ($item && ($user !== null || $item->isDraft() === false)) {
                $collection[] = $item;
            }
        }

        return snippet('recipes-gallery', [
            'recipes' => new Collection($collection),
        ], true);
    },
];
