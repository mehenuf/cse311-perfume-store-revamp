    </main>

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
