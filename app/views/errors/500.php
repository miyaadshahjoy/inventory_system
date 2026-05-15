<?php
http_response_code(500);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>500 - Server Error</title>

    <link rel="stylesheet" href="/assets/css/app.css" />
  </head>

  <body>
    <div class="error-page">
      <div class="error-card">
        <h1 class="error-code">500</h1>

        <h2 class="error-title">Internal Server Error</h2>

        <p class="error-message">Something went wrong on our side.</p>

        <a href="/dashboard" class="error-button"> Return Dashboard </a>
      </div>
    </div>
  </body>
</html>
