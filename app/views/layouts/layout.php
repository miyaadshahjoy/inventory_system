<?php
$content ??= '';
?>

<?php require_once __DIR__ . '/header.php'; ?>

<div class="app-layout">

    <button id="sidebarToggle" class="sidebar-toggle">
        <svg width="16px" height="16px" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="#343a40">
            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
            <g id="SVGRepo_iconCarrier">
                <title>collapse-horizontal-solid</title>
                <g id="Layer_2" data-name="Layer 2">
                    <g id="invisible_box" data-name="invisible box">
                        <rect width="48" height="48" fill="none"></rect>
                    </g>
                    <g id="icons_Q2" data-name="icons Q2">
                        <g>
                            <path
                                d="M32.6,22.6a1.9,1.9,0,0,0,0,2.8l5.9,6a2.1,2.1,0,0,0,2.7.2,1.9,1.9,0,0,0,.2-3L38.8,26H44a2,2,0,0,0,0-4H38.8l2.6-2.6a1.9,1.9,0,0,0-.2-3,2.1,2.1,0,0,0-2.7.2Z">
                            </path>
                            <path
                                d="M15.4,25.4a1.9,1.9,0,0,0,0-2.8l-5.9-6a2.1,2.1,0,0,0-2.7-.2,1.9,1.9,0,0,0-.2,3L9.2,22H4a2,2,0,0,0,0,4H9.2L6.6,28.6a1.9,1.9,0,0,0,.2,3,2.1,2.1,0,0,0,2.7-.2Z">
                            </path>
                            <path d="M26,6V42a2,2,0,0,0,4,0V6a2,2,0,0,0-4,0Z"></path>
                            <path d="M22,42V6a2,2,0,0,0-4,0V42a2,2,0,0,0,4,0Z"></path>
                        </g>
                    </g>
                </g>
            </g>
        </svg>

    </button>

    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <!-- Show total low stock and out stock -->
        <div class="header-info">

            <?php if ($total_low_stocks > 0): ?>
                <button class="low-stock">
                    <span>Total low stocks: </span>
                    <?= $total_low_stocks ?>
                </button>

            <?php endif; ?>

            <?php if ($total_out_stocks > 0): ?>
                <button class="out-stock">
                    <span>Total out stocks: </span>
                    <?= $total_out_stocks ?>
                </button>

            <?php endif; ?>

            <!-- Current date -->
            <?= date('M d, Y H:i A') ?>
        </div>

        <!-- ######################################## -->
        <!-- # SHOW CONTENT -->
        <?= $content ?>
        <!-- ######################################## -->
    </main>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>