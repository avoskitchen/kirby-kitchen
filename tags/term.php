<?php

/**
 * Creates a link to a term in the knowledge base.
 */

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Toolkit\Str;

return [
    'attr' => [
        'text',
        'class',
        'title',
    ],
    'html' => function ($tag) {

        $base = site()->children()->filterBy('template', 'knowledge')->first();

        $term = $tag->attr('term');

        if ($hashPos = strpos($term, '#') !== false) {
            $hash = substr($term, $hashPos);
            $term = substr($term, 0, $hashPos);
        } else {
            $hash = '';
        }
        
        $target = "{$base->id()}/{$term}";
        $targetPage = $base->findPageOrDraft($term);

        $link = url($target, $tag->attr('lang')) . $hash;
        $text = $tag->attr('text');

        if (empty($text)) {
            if ($targetPage !== null) {
                $text = $targetPage->title();
            } else {
                $text = '⚠️ ' . $term . '';
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
        ]);

    },
];
