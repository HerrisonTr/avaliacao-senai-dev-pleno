<?php
$pageConfig = $pageConfig ?? [];
$pageScripts = $pageConfig['scripts'] ?? [];
?>
            <footer class="app-footer">
                <div class="float-end d-none d-sm-inline">Desenvolvido com 💌 por Herrison Trugilho</div>
                <strong>SENAI SC</strong>
            </footer>
        
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="/assets/js/adminlte/adminlte.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script type="module" src="/assets/js/ui.js"></script>
        
        <?php foreach ($pageScripts as $pageScript): ?>
            <script type="module" src="<?= htmlspecialchars($pageScript, ENT_QUOTES, 'UTF-8'); ?>"></script>
        <?php endforeach; ?>

        <script type="module">
            import { auth } from '/assets/js/auth.js';
            import { applySidebarRoutes } from '/assets/js/sidebar.js';

            applySidebarRoutes();
            const logoutButton = document.querySelector('#logout-button');

            if (logoutButton) {
                logoutButton.addEventListener('click', async () => {
                    logoutButton.disabled = true;

                    try {
                        await auth.logout();
                    } finally {
                        logoutButton.disabled = false;
                    }
                });
            }
        </script>
    </body>

</html>
