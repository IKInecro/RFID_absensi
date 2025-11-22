<?php
session_start();
if (isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guest_login'])) {
        $_SESSION['role'] = 'user';
        $_SESSION['username'] = 'Tamu';
        header('Location: index.php');
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hardcoded credentials as requested
    if ($username === 'MARCO' && $password === '123') {
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'MARCO';
        header('Location: index.php');
        exit;
    } elseif ($username === 'user' && $password === 'user') { // Simple user account for testing/demo
        $_SESSION['role'] = 'user';
        $_SESSION['username'] = 'User';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - RFID System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
    <div class="p-8">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-blue-600 text-white mb-4 shadow-lg shadow-blue-500/30">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Selamat Datang</h1>
        <p class="text-gray-500 mt-2 text-sm">Silakan login untuk mengakses sistem</p>
      </div>

      <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
              </div>
            </div>
      <?php endif; ?>

      <form method="POST" class="space-y-5">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input type="text" id="username" name="username" required
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
            placeholder="Masukkan username">
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" id="password" name="password" required
            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
            placeholder="Masukkan password">
        </div>

        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-[1.02] shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
          </svg>
          Masuk sebagai Admin
        </button>
      </form>

      <div class="mt-4 relative">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-2 bg-white text-gray-500">Atau</span>
        </div>
      </div>

      <form method="POST" class="mt-4">
        <input type="hidden" name="guest_login" value="1">
        <button type="submit"
          class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-[1.02] flex items-center justify-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Masuk sebagai Tamu
        </button>
      </form>
    </div>
    <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 text-center">
      <p class="text-xs text-gray-500">&copy; <?= date('Y') ?> RFID Attendance System</p>
    </div>
  </div>
</body>

</html>