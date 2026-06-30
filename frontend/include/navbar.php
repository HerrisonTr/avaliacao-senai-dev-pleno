<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$navbarLinks = [
    [
        'label' => 'Dashboard',
        'href' => '/pages/dashboard.php',
    ],
    [
        'label' => 'Usuários',
        'href' => '/pages/usuarios.php',
    ],
];
?>
<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list"></i>
        </a>
    </li>
    <?php foreach ($navbarLinks as $navbarLink): ?>
        <?php $isActive = $currentPath === $navbarLink['href']; ?>
        <li class="nav-item d-none d-md-block">
            <a href="<?= htmlspecialchars($navbarLink['href'], ENT_QUOTES, 'UTF-8'); ?>"
                class="nav-link<?= $isActive ? ' active' : ''; ?>">
                <?= htmlspecialchars($navbarLink['label'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
