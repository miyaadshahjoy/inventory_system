<?php
ob_start();
?>

<h2>HOMEPAGE</h2>

<?php
$content = ob_get_clean();

require_once __DIR__ . '/layouts/layout.php';
?>