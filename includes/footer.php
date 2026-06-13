            </main>

            <footer class="app-footer">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?></p>
            </footer>
        </div>
    </div>

    <?php require __DIR__ . '/mobile_nav.php'; ?>

    <?php
    $loadLeaflet = $loadLeaflet ?? true;
    $loadChart = $loadChart ?? false;
    renderVendorScripts($loadLeaflet, $loadChart);
    ?>
    <script src="<?= assetUrl('js/app.js') ?>"></script>
    <?php renderPwaScripts(); ?>
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
