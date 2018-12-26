<?php snippet('header') ?>

<section class="knowledge">
  <h1><?= $page->title()->escape() ?></h1>
    
  <div class="knowledge-items">
    <?php foreach ($page->getItemsGroupedByCategory() as $category): ?>
      <div class="knowledge-category">
        <h2><?= $category->title()->escape() ?></h2>
        <ul>
          <?php foreach($category->items() as $item): ?>
            <li><a href="<?= $item->url() ?>"><?= $item->title() ?></a></li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endforeach ?>
  </div>
  </section>

<?php snippet('footer') ?>
