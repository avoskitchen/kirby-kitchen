<ul class="recipes-gallery"<?php if ($item->isDraft()) {
    echo ' style="background: repeating-linear-gradient(-45deg, rgba(209, 100, 100, .2), rgba(209, 100, 100, .2) 5px, transparent 5px, transparent 10px);"';
} ?>>
  <?php foreach ($recipes as $item): ?>
    <li><a href="<?= $item->url() ?>"><?= $item->title()->html() ?></a></li>
  <?php endforeach ?>
</ul>
