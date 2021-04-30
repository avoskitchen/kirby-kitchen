<?php

use Kirby\Cms\Url;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Str;

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
        $recipe = $tag->value;

        
        if (strstr($recipe, '#') !== false) {
            list($recipe, $hash) = explode('#', $recipe);
            $hash = "#{$hash}";
        } else {
            $hash = '';
        }

        if (strstr($recipe, '/') !== false) {
            // absolute path
            $path = $recipe;
            $targetPage = $tag->site()->find($path);
        } else {
            // just the slug
            $base = ($parentPage->template()->name() === 'recipes')
                ? $parentPage
                : $tag->site()->children()->filterBy('template', 'recipes')->first();

            $targetPage = $base->findPageOrDraft($recipe);
            $path = "{$base->id()}/{$recipe}";
        }

        $link = url($path, $tag->attr('lang')) . $hash;
        $text = $tag->attr('text');

        if (empty($text)) {
            if ($targetPage !== null) {
                $text = $targetPage->title();
            } else {
                $text = '⚠️ ' . pathinfo($link, PATHINFO_FILENAME) . '';
            }
        }

        if ($targetPage !== null && $targetPage->isDraft() === true) {
            if (($user = kirby()->user()) && $user->id() !== 'nobody') {
                // Add some styling to link, that points to draft-page
                // for logged-in users.
                $style = 'background: repeating-linear-gradient(-45deg, rgba(209, 100, 100, .3), rgba(209, 100, 100, .3) 5px, transparent 5px, transparent 10px);';
            } else {
                // Don’t generate any link at all for normal visitors.
                return $text;
            }
        }

        return Html::a($link, $text, [
            'class' => $tag->attr('class'),
            'title' => $tag->attr('title'),
            'style' => $style ?? null,
        ]);

    },
];
