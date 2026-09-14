            </main>
        </div><!-- /.main -->
    </div><!-- /.app -->

    <button class="back-to-top" type="button" data-back-to-top data-visible="false" aria-label="Back to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>

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
