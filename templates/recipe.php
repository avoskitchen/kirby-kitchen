<?php snippet('header') ?>
<?= css(Kirby::plugin('avoskitchen/kitchen')->mediaUrl() . '/css/kitchen.css') ?>
<?php /* @var AvosKitchen\Kitchen\Models\RecipePage $page */ ?>
<?php /* @var Kirby $kirby */ ?>

<article class="recipe">

  <header class="recipe-header">
    <h1><?= $page->title()->escape() ?></h1>

    <?php if ($image = $page->cover()->toFile()): ?>
      <figure class="recipe-cover">
        <?= $image->resize(1000)->html() ?>
      </figure>
    <?php endif ?>
  </header>

  <?php if($page->text()->isNotEmpty()): ?>
    <div class="recipe-text">
      <?= $page->text()->kirbytext() ?>
    </div>
  <?php endif ?>
  
  <div class="recipe-preparation">
    <?php if ($page->ingredients()->isNotEmpty()): ?>
      <div class="recipe-ingredients / js-kitchen-ingredients">
        <h2>Zutaten</h2>
        <?= $page->yieldFormatted() ?>
        <?= $page->ingredientsFormatted() ?>
      </div>
    <?php endif ?>

    <?php if ($page->instructions()->isNotEmpty()): ?>
      <div class="recipe-instructions">
        <h2>Zubereitung</h2>
        <?= $page->instructionsFormatted() ?>
      </div>
    <?php endif ?>
    
    <?php if ($page->tips()->isNotEmpty()): ?>
      <div class="recipe-tips">
        <h2>Tipps &amp; Varianten</h2>
        <?= $page->tipsFormatted() ?>
      </div>
    <?php endif ?>
  </div>

  <footer class="recipe-footer">
    <h2>Rezeptinformationen</h2>

    <?php if ($page->source()->isNotEmpty()): ?>
      <div class="recipe-source">
        <h3>Quelle/Inspiration:</h3>
        <?= $page->source()->kirbytext() ?>
      </div>
    <?php endif ?>

    <div class="recipe-meta">
      <p><strong>Kategorie:</strong> <?= $page->categoryTitle()->escape() ?></p>

      <?php if($page->tags()->isNotEmpty()): ?>
        <p><strong>Tags:</strong> <?= implode(', ', $page->tags()->split()) ?></p>
      <?php endif ?>

      <?php if ($page->cuisines()->isNotEmpty()): ?>
        <p><strong>Küchen:</strong> <?= $page->cuisinesFormatted() ?></p>
      <?php endif ?>

      <?php if ($page->lastEdited()->isNotEmpty()): ?>
        <p><strong>Zuletzt bearbeitet:</strong> <?= $page->lastEdited()->toDate('d.m.Y H:i') ?>&nbsp;Uhr</p>
      <?php endif ?>

      <?php if ($kirby->user()): ?>
        <p><a href="<?= $page->panel()->url() ?>" class="recipe-edit-link">Bearbeiten</a></p>
      <?php endif ?>
    </div>
  </footer>

</article>

<?php
$assetsUrl = Kirby::plugin('avoskitchen/kitchen')->mediaUrl();
echo js("{$assetsUrl}/js/kitchen.js", [
  'data-assets-url' => $assetsUrl,
]);
?>

<?php snippet('footer') ?>
