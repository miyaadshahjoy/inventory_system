<?php
$content ??= '';
?>

<?php require_once __DIR__ . '/header.php'; ?>

<div class="app-layout">

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <?= $content ?>
    </main>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>