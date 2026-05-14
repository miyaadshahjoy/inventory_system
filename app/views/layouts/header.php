<?php
require_once __DIR__ . '/../../core/Session.php';
?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Homepage</title>
  </head>

  <body>
    <header>
      <!-- Show flash messages -->
      <?php if (Session::hasFlash('success')): ?>
        <div>
          &#10004;
          <?php echo Session::flashGet('success'); ?>
        </div>
      <?php endif; ?>

      <?php if (Session::hasFlash('error')): ?>
        <div>
          &#10006;
          <?php echo Session::flashGet('error'); ?>
        </div>
      <?php endif; ?>
      <?php if (Session::hasFlash('warning')): ?>
        <div>
          &#33;
          <?php echo Session::flashGet('warning') ?>
        </div>
      <?php endif; ?>

      <?php if (Session::hasFlash('info')): ?>
        <div>
          &#161;
          <?php echo Session::flashGet('info') ?>
        </div>
      <?php endif; ?>
    </header>