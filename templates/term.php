<?php snippet('header') ?>

<article class="term">

  <header class="term-header">
    <h1><?= $page->title()->escape() ?></h1>
  </header>

  <div class="term-text">
    <?= $page->text()->kirbytext() ?>
  </div>
</article>

<?php snippet('footer') ?>
