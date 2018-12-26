<?php

/**
 * Inserts a Kitchen timer tag.
 */
return [
    'attr' => [
        'text',
        'title',
    ],
    'html' => function ($tag) {

        $timer = $tag->attr('timer');
        $text = $tag->attr('text');
        $title = $tag->attr('title');

        if ($text === null) {
            $text = htmlspecialchars($timer);
        }

        $attr = [
            'data-timer' => $timer,
        ];

        if ($title !== null) {
            $attr['data-timer-title'] = htmlspecialchars($title);
        }

        return html::tag('span', $text, $attr);
    },
];
