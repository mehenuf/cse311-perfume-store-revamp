    </main>

    <?php $basePath = isset($basePath) ? $basePath : ''; ?>
    <script>
        // '' at the web root, '../' one level down (brands/) -- store.js needs
        // this to reach functions/cart-function.php from any page depth.
        window.STORE_BASE_PATH = <?= json_encode($basePath, JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="<?= $basePath ?>assets/js/theme.js?v=<?= @filemtime(__DIR__ . '/../assets/js/theme.js') ?>" defer></script>
    <script src="<?= $basePath ?>assets/js/store.js?v=<?= @filemtime(__DIR__ . '/../assets/js/store.js') ?>" defer></script>

    <?php if (isset($_SESSION['message']) && $_SESSION['message'] !== '') { ?>
        <script>
            window.addEventListener('load', function () {
                if (window.storeToast) {
                    window.storeToast(<?= json_encode($_SESSION['message'], JSON_UNESCAPED_UNICODE) ?>, 'success');
                }
            });
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php } ?>

</body>

</html>
