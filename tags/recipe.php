<?php

use Kirby\Toolkit\Html;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Url;

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
            list($recipe, $hash) = $map = explode('#', $recipe);
            $hash = "#{$hash}";
        } else {
            $hash = '';
        }

        if(strstr($recipe, '/')) {
            // absolute path
            $target = $recipe;
        } else {
            // just the slug
            $base = ($parentPage->template()->name() === 'recipes') ? $parentPage->id() : site()->children()->filterBy('template', 'recipes')->first()->id();
            $target = $base . '/' . $recipe;
        }

        $link = url($target, $tag->attr('lang')) . $hash;
        $text = $tag->attr('text');

        if (empty($text)) {
            $targetPage = site()->find($target);
            if ($targetPage) {
                $text = $targetPage->title();
            } else {
                $text = '[⚠️ Missing page: ' . $link . ']';
            }
        }

        if (Str::isURL($text)) {
            $text = Url::short($text);
        }

        return Html::a($link, $text, [
            'class' => $tag->attr('class'),
            'title' => $tag->attr('title'),
        ]);

    },
];
