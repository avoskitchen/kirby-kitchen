<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use Kirby\Cms\Page;

App::plugin('avoskitchen/kitchen', [

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

    'blueprints' => [

        # fields

        'kitchen/fields/category-options' => __DIR__ . '/blueprints/fields/category-options.yml',
        'kitchen/fields/category' => __DIR__ . '/blueprints/fields/category.yml',
        'kitchen/fields/cover' => __DIR__ . '/blueprints/fields/cover.yml',
        'kitchen/fields/cuisine-options' => __DIR__ . '/blueprints/fields/cuisine-options.yml',
        'kitchen/fields/cuisines' => __DIR__ . '/blueprints/fields/cuisines.yml',
        'kitchen/fields/lastedited' => __DIR__ . '/blueprints/fields/lastedited.yml',
        'kitchen/fields/tags' => __DIR__ . '/blueprints/fields/tags.yml',
        'kitchen/fields/unit-options' => __DIR__ . '/blueprints/fields/unit-options.yml',

        # pages

        'pages/knowledge' => __DIR__ . '/blueprints/pages/knowledge.yml',
        'pages/recipe' => __DIR__ . '/blueprints/pages/recipe.yml',
        'pages/recipes' => __DIR__ . '/blueprints/pages/recipes.yml',
        'pages/term' => __DIR__ . '/blueprints/pages/term.yml',

        # pages are also registred with a namespaced alias, so they can be extended
        # in your own page blueprints

        'kitchen/pages/knowledge' => __DIR__ . '/blueprints/pages/knowledge.yml',
        'kitchen/pages/recipe' => __DIR__ . '/blueprints/pages/recipe.yml',
        'kitchen/pages/recipes' => __DIR__ . '/blueprints/pages/recipes.yml',
        'kitchen/pages/term' => __DIR__ . '/blueprints/pages/term.yml',

        # sections

        'kitchen/sections/files' => __DIR__ . '/blueprints/sections/files.yml',
        'kitchen/sections/recipe-content' => __DIR__ . '/blueprints/sections/recipe-content.yml',
        'kitchen/sections/recipe-meta' => __DIR__ . '/blueprints/sections/recipe-meta.yml',
    ],

    'hooks' => [
        'page.create:before' => function (Page $page, array $input) {
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
        'page.create:after' => function (Page $page) {
            switch ($page->template()) {
                case 'term':
                case 'recipe':
                    // inspired by the kirby last edited field by dennis kerzig (released under the mit license)
                    // https://github.com/wottpal/kirby-last-edited
                    $now = date('Y-m-d H:i:s');
                    $page->update([
                        'created' => $now,
                        'lastEdited' => $now,
                    ], null);

                    break;
            }
        },
        'page.update:after' => function (Page $newPage, Page $oldPage) {
            switch ($newPage->template()) {
                case 'term':
                case 'recipe':
                    $newPage->update([
                        'lastEdited' => date('Y-m-d H:i:s'),
                    ], null);

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
        'yield' => __DIR__ . '/snippets/yield.php',
    ],

    'tags' => [
        'recipe' => require __DIR__ . '/tags/recipe.php',
        'term' => require __DIR__ . '/tags/term.php',
    ],

    'templates' => [
        'recipe' => __DIR__ . '/templates/recipe.php',
        'recipes' => __DIR__ . '/templates/recipes.php',
        'knowledge' => __DIR__ . '/templates/knowledge.php',
        'term' => __DIR__ . '/templates/term.php',
    ],
]);
