<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/ByteClass/config/constants.php';
$page_title = $page_title ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="ByteClass — Learn. Build. Grow. Tech education platform." />
  <title><?= htmlspecialchars($page_title) ?> — <?= APP_NAME ?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary:   { DEFAULT: '#4F46E5', dark: '#4338CA', light: '#EEF2FF' },
            secondary: { DEFAULT: '#06B6D4', dark: '#0891B2', light: '#ECFEFF' },
            accent:    { DEFAULT: '#F59E0B', dark: '#D97706', light: '#FFFBEB' },
          },
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
          },
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css" />
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
