<?php
http_response_code(403);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>403 - Forbidden</title>

    <link rel="stylesheet" href="/assets/css/app.css" />
  </head>

  <body>
    <div class="error-page">
      <div class="error-card">
        <h1 class="error-code">403</h1>

        <h2 class="error-title">Access Forbidden</h2>

        <p class="error-message">
          You do not have permission to access this page.
        </p>

        <a href="/dashboard" class="error-button"> Return Dashboard </a>
      </div>
    </div>
  </body>
</html>
