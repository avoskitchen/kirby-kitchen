# Kitchen for Kirby 3

![GitHub release](https://img.shields.io/github/release/avoskitchen/kirby-kitchen.svg?maxAge=2592000)

⚠️ This is beta software, use at you own risk! ⚠️

A complete recipe manager with knowledge base, we wrote for ourselves. This is currently more or less
a semi-public plugin, as we cannot provide any support for it. But if you’re
interested in using it on your site, feel free to use or adapt the code to your
needs. But use at you own risk, of course ;-) If you got any idea of how this can be improved, feel free to create a ticket or pull request.

## How it works

The plugin provides a set of templates that can be used as a starting point for your recipe collection, food blog or whatsoever. To start using the plugin, just tune the settings below to your needs, then go to the panel and create two new top-level pages:

- One or more pages with the `recipes`  template. Each of these holds a recipe collection. For most users, one recipe directory should be enough. Note that having more than one recipe page 
- If you want to use the knowledge-base, also create a page with the `knowledge` template. The knowledge base can be used to store information about different ingredients, preparation methods etc. There can only be one knowledge base on your site.

## System Requirements

- Kirby 4.1.0+
- PHP 8.1+
- A locale must be set (for future versions)
- `intl` must be used as date formatter

## Installation

```
composer require avoskitchen/kirby-kitchen
```

## Available Options

| Key | Type | Default | Description |
|:----|:-----|:--------|:------------|
| fractions | bool | `true` | Uses fractions to transform values like 0.5 into ½ in the ingredients list. Disable, if this does not look well in your font. |
| decimals | int | `2` | Sets the number of decimals to be shown in the ingredients list, if odd numbers appear e.g. through changing the recipe yield. |
| decimalPoint | string | `'.'` | Sets the decimal point for ingredient amounts. Change this according to the language of your site. |
| thousandsSeparator | string | `','` | Sets the thousands separator for ingredient amounts. Change this according to the language of your site. |

## KirbyTags

Use the following Kirbytags to spice up your recipes:

### `(recipe: …)`
Embeds a recipe (⚠️ not ready yet!)

#### Attributes

| Name | Type | Required | Description |
|:-----|:-----|:---------|:------------|
| recipe | string | true | The slug of the target recipe. If you  are linking from one recipe to another, the current recipe directory is used to search for the recipe. If you got multiple recipe directory and you are linking to a recipe from any other page, you have to specify the full slug of the recipe (e.g. `(recipe: baking-recipes/cookies)`. |
| class | string | false | Additional CSS classes to add to the wrapper element. |
| title | string | false | Optional title attribute. |

### `(term: …)`

Inserts a link to a term page. This could be extended with a tooltip (like e.g. on Wikipedia) or additional features. Currently, it just provides a handier version of the link tag.

#### Attributes

| Name | Type | Required | Description |
|:-----|:-----|:---------|:------------|
| term | string | true | Slug of the term page to link to |
| class | string | false | Additional CSS classes to add to the link element. |
| title | string | false | Optional title attribute. |

## Make it Yours

The Kitchen plugin comes with its own set of default templates, located in `site/plugins/kitchen/templates`. You can override these defaults by placing template files with the same name into `site/templates/`:

| Template | Description |
|:---------|:------------| 
| recipes.php | Displays all entries of the recipes database grouped by category.
| recipe.php | Displays a single recipe. |
| knowledge.php | Displays all entries of the knowledge base grouped by category. |
| term.php | Displays a single term from the knowledge base.

You can also extend the plugin’s blueprints. Let’s say you want to extend the `recipes` blueprint; just create a new file `site/blueprints/pages/recipes.yml` and add a tab to the blueprint, containing your own field definitions:

```yaml
extends: kitchen/pages/recipes
tabs:
  settings:
    label: Settings
    icon: settings
    fields:
      hero:
        label: Hero image
        type: files
        multiple: false
      … 
```

## Credits

This plugin uses code from the following Kirby 3 plugins:

- [Kirby Last Edited]( https://github.com/wottpal/kirby-last-edited/) by Dennis Kerzig.

## License

This plugin is licensed under the MIT license with the exception of the included icons from the Nucleo set. See index.js for further information on the icons’ license.

However, it is strongly discouraged to use it in any project, that promotes racism, sexism, homophobia, animal abuse or any other form of hate-speech or violence.
