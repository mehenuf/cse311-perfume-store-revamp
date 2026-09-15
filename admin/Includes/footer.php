            </main>
        </div><!-- /.main -->
    </div><!-- /.app -->

    <button class="back-to-top" type="button" data-back-to-top data-visible="false" aria-label="Back to top">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>

    <script>
        // Read by the inline price/stock editor's fetch() call in admin.js --
        // that AJAX request has no <form> to carry a hidden csrf_token field.
        window.ADMIN_CSRF_TOKEN = <?= json_encode(csrfToken(), JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="../assets/js/theme.js?v=<?= @filemtime(__DIR__ . '/../../assets/js/theme.js') ?>" defer></script>
    <script src="assets/js/admin.js?v=<?= @filemtime(__DIR__ . '/../assets/js/admin.js') ?>" defer></script>

    <?php if (isset($_SESSION['message']) && $_SESSION['message'] !== '') { ?>
        <script>
            window.addEventListener('load', function () {
                if (window.adminToast) {
                    window.adminToast(<?= json_encode($_SESSION['message'], JSON_UNESCAPED_UNICODE) ?>, <?= json_encode($_SESSION['message_kind'] ?? 'success') ?>);
                }
            });
        </script>
        <?php unset($_SESSION['message'], $_SESSION['message_kind']); ?>
    <?php } ?>

</body>

</html>
