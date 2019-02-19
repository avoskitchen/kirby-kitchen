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
        $target =  "{$base}/{$term}";
        $link = url($target, $tag->attr('lang'));
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
