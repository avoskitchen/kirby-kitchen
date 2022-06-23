<?php

@include_once __DIR__ . '/vendor/autoload.php';

use AvosKitchen\Kitchen\Api;
use Kirby\Cms\App as Kirby; // Satisfy linter

$kirby = kirby();

Kirby::plugin('avoskitchen/kitchen', [

    'options' => [
        'fractions' => true,
        'decimals' => 2,
        'decimalPoint' => '.',
        'thousandsSeparator' => ',',
        'ingredientClass' => 'ingredient',
        'ingredientGroupClass' => 'ingredient-group',
        'ingredientAmountClass' => 'ingredient-amount',
        'ingredientItemClass' => 'ingredient-item',
    ],

    'api' => [
        'routes' => [
            [
                'pattern' => 'plugin-kitchen/(:any)',
                'action' => function (string $job) {
                    return Api::api($job);
                },
            ],
        ],
    ],

    'blueprints' => [

        # Fields

        'kitchen/fields/category-options' => __DIR__ . '/blueprints/fields/category-options.yml',
        'kitchen/fields/category' => __DIR__ . '/blueprints/fields/category.yml',
        'kitchen/fields/cover' => __DIR__ . '/blueprints/fields/cover.yml',
        'kitchen/fields/cuisine-options' => __DIR__ . '/blueprints/fields/cuisine-options.yml',
        'kitchen/fields/cuisines' => __DIR__ . '/blueprints/fields/cuisines.yml',
        'kitchen/fields/lastedited' => __DIR__ . '/blueprints/fields/lastedited.yml',
        'kitchen/fields/tags' => __DIR__ . '/blueprints/fields/tags.yml',
        'kitchen/fields/unit-options' => __DIR__ . '/blueprints/fields/unit-options.yml',

        # Pages

        'pages/knowledge' => __DIR__ . '/blueprints/pages/knowledge.yml',
        'pages/recipe' => __DIR__ . '/blueprints/pages/recipe.yml',
        'pages/recipes' => __DIR__ . '/blueprints/pages/recipes.yml',
        'pages/term' => __DIR__ . '/blueprints/pages/term.yml',

        # Pages are also registred with a namespaced Alias, so they can be extended
        # in your own page blueprints.

        'kitchen/pages/knowledge' => __DIR__ . '/blueprints/pages/knowledge.yml',
        'kitchen/pages/recipe' => __DIR__ . '/blueprints/pages/recipe.yml',
        'kitchen/pages/recipes' => __DIR__ . '/blueprints/pages/recipes.yml',
        'kitchen/pages/term' => __DIR__ . '/blueprints/pages/term.yml',

        # Sections

        'kitchen/sections/files' => __DIR__ . '/blueprints/sections/files.yml',
        'kitchen/sections/recipe-content' => __DIR__ . '/blueprints/sections/recipe-content.yml',
        'kitchen/sections/recipe-meta' => __DIR__ . '/blueprints/sections/recipe-meta.yml',
    ],

    'fields' => [
        'kitchen-lastedited' => [
            'props' => [
                'value' => function ($value = null) {
                    return $value;
                },
            ],
            'computed' => [
                'modified' => function () {
                    return $this->model()->modified();
                },
            ],
        ],

        'kitchen-ajaxbutton' => [
            'props' => [
                'label' => function (string $label = null) {
                    return $label;
                },
                'progress' => function (string $progress = null) {
                    return $progress;
                },
                'job' => function (string $job = null) {
                    return "plugin-kitchen/{$job}";
                },
                'hideif' => function (string $job = null) {
                    return ! empty($job) ? "plugin-kitchen/{$job}" : null;
                },
                'cooldown' => function () {
                    return 2000;
                },
            ],
        ],
    ],

    'hooks' => [
        'page.create:before' => function ($page, $input) {
            switch ($input['template']) {

                case 'knowledge':
                    $knowledgePages = site()->children()->filterBy('template', 'knowledge');

                    if ($knowledgePages->count() > 0) {
                        throw new Exception('Your site can only contain one knowledge base page. Please delete the page "' . $knowledgePages->first()->title() . '" before creating a new knowledge base page.');
                    }

                    if ($page->parent() !== null) {
                        throw new Exception('A knowledge base page can only be created at the top-level of your site.');
                    }

                    break;
            }
        },
        'page.create:after' => function ($page) {
            switch ($page->template()) {
                case 'term':
                case 'recipe':
                    // Inspired by the Kirby Last Edited Field by Dennis Kerzig (released under the MIT license)
                    // https://github.com/wottpal/kirby-last-edited
                    $now = date('Y-m-d H:i:s');
                    $page->update([
                        'created' => $now,
                        'lastEdited' => $now,
                    ], false);

                    break;
            }
        },
        'page.update:after' => function ($newPage, $oldPage) {
            switch ($newPage->template()) {
                case 'term':
                case 'recipe':
                    $newPage->update([
                        'lastEdited' => date('Y-m-d H:i:s'),
                    ], false);

                    break;
            }
        },
    ],

    'pageModels' => [
        'knowledge' => AvosKitchen\Kitchen\Models\KnowledgePage::class,
        'recipe' => AvosKitchen\Kitchen\Models\RecipePage::class,
        'recipes' => AvosKitchen\Kitchen\Models\RecipesPage::class,
        'term' => AvosKitchen\Kitchen\Models\TermPage::class,
    ],

    'snippets' => [
        'recipes-gallery' => __DIR__ . '/snippets/recipes-gallery.php',
        'yield' => __DIR__ . '/snippets/yield.php',
    ],

    'tags' => [
        'recipe' => require __DIR__ . '/tags/recipe.php',
        'recipes-gallery' => require __DIR__ . '/tags/recipes-gallery.php',
        'term' => require __DIR__ . '/tags/term.php',
    ],

    'templates' => [
        'recipe' => __DIR__ . '/templates/recipe.php',
        'recipes' => __DIR__ . '/templates/recipes.php',
        'knowledge' => __DIR__ . '/templates/knowledge.php',
        'term' => __DIR__ . '/templates/term.php',
    ],
]);
