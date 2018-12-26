<form class="yield" action="<?= $page->url() ?>" method="GET">
  <input type="number"
         inputmode="numeric"
         pattern="[0-9]*"
         step="1"
         min="1"
         name="yield"
         max="1000"
         value="<?= $yield ?>"
         id="yield-input">
  <label for="yield-input"><?= $unit ?></label>
  <button type="submit">Aktualisieren</button>
  <?php if ($isDefaultYield): ?>
    <a href="<?= $page->url() ?>">(zurücksetzen)</a>
  <?php endif ?>
</form>
