            </main>
        </div><!-- /.main -->
    </div><!-- /.app -->

    <script src="../assets/js/theme.js?v=<?= @filemtime(__DIR__ . '/../../assets/js/theme.js') ?>" defer></script>
    <script src="assets/js/admin.js?v=<?= @filemtime(__DIR__ . '/../assets/js/admin.js') ?>" defer></script>

    <?php if (isset($_SESSION['message']) && $_SESSION['message'] !== '') { ?>
        <script>
            window.addEventListener('load', function () {
                if (window.adminToast) {
                    window.adminToast(<?= json_encode($_SESSION['message'], JSON_UNESCAPED_UNICODE) ?>, 'success');
                }
            });
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php } ?>

</body>

</html>
