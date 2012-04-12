<li class="resource">
  <?php if (isset($resource)) : ?>
    <div class="heading">
        <h2><?php echo $resource; ?></h2>
    </div>
  <?php endif; ?>
  <ul class="endpoints">
    <li class="endpoint">
        <ul class="operations">
            <?php echo $content; ?>
        </ul>
    </li>
  </ul>
</li>
