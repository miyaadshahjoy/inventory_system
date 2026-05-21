<?php $title ??= 'Inventory System' ?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/css/style.css">
    <title><?= $title ?></title>
  </head>

  <body>
    <header>
      <!-- Show flash messages -->
      <?php if (Session::hasFlash('success')): ?>
        <div class="flash flash-success">
           <?= Session::flashGet('success'); ?>
        </div>
      <?php endif; ?>

      <?php if (Session::hasFlash('error')): ?>
        <div class="flash flash-error">
          ❌ <?= Session::flashGet('error'); ?>
        </div>
      <?php endif; ?>

      <?php if (Session::hasFlash('warning')): ?>
        <div class="flash flash-warning">
          ❕ <?= Session::flashGet('warning'); ?>
        </div>
      <?php endif; ?>

      <?php if (Session::hasFlash('info')): ?>
        <div class="flash flash-info">
          &#8505; <?= Session::flashGet('info'); ?>
        </div>
      <?php endif; ?>
    </header>