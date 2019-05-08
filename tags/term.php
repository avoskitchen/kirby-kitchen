<?php

/**
 * Creates a link to a term in the knowledge base.
 */
return [
    'attr' => [
        'text',
        'class',
        'title',
    ],
    'html' => function ($tag) {

        $base = site()->children()->filterBy('template', 'knowledge')->first()->id();

        $term = $tag->attr('term');

        if ($hashPos = strpos($term, '#') !== false) {
            $hash = substr($term, $hashPos);
            $term = substr($term, 0, $hashPos);
        } else {
            $hash = '';
        }
        
        $target = "{$base}/{$term}";

        $link = url($target, $tag->attr('lang')) . $hash;
        $text = $tag->attr('text');

        if (empty($text)) {
            $targetPage = site()->find($target);
            if ($targetPage) {
                $text = $targetPage->title();
            } else {
                $text = "[⚠️ Missing term: {$term}]";
            }
        }

        if (Str::isURL($text)) {
            $text = Url::short($text);
        }

        return Html::a($link, $text, array(
            'class' => $tag->attr('class'),
            'title' => $tag->attr('title'),
        ));

    },
];
