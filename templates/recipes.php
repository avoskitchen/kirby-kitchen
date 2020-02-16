<?php snippet('header') ?>

<h1><?= $page->title()->escape() ?></h1>
  
<div class="recipes mt1">
  <?php foreach ($page->getItemsGroupedByCategory() as $category): ?>
    <div class="recipes-category">
      <h2><?= $category->title()->escape() ?></h2>
      <ul>
        <?php foreach($category->items() as $item): ?>
          <li><a href="<?= $item->url() ?>"><?= $item->title() ?></a></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endforeach ?>
</div>

<?php snippet('footer') ?>
