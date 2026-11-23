//naaryah naraine footer.php
/main>
<script src="/js/common.js" defer></script>

<?php if (!empty($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>

        <script src="<?= e($script) ?>" defer></script>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>