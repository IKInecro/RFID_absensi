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
    <title>Login - SMAPIJ ABSENSI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .input-group:focus-within label {
            color: #2563eb;
        }

        .input-group:focus-within svg {
            color: #2563eb;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Overlay Dark -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-gray-900/80 to-black/90 z-0"></div>

    <div class="w-full max-w-[420px] relative z-10">
        <!-- Logo Container -->
        <div class="flex justify-center mb-8 animate-float">
            <div
                class="relative w-32 h-32 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center shadow-2xl ring-4 ring-white/30">
                <img src="assets/img/pgri_transparent.png" alt="Logo SMAPIJ"
                    class="w-24 h-24 object-contain drop-shadow-lg">
            </div>
        </div>

        <!-- Card -->
        <div
            class="glass-card rounded-3xl shadow-2xl overflow-hidden transform transition-all hover:scale-[1.01] duration-500">
            <div class="p-8 sm:p-10">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Selamat Datang</h1>
                    <p class="text-lg font-semibold text-blue-700">di SMAPIJ ABSENSI</p>
                    <p class="text-gray-500 text-sm mt-2">Silakan login untuk memulai sesi Anda</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50/90 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl animate-pulse">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div class="input-group space-y-1">
                        <label for="username"
                            class="block text-xs font-bold text-gray-500 uppercase tracking-wider transition-colors">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 transition-colors" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" required
                                class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200 font-medium"
                                placeholder="Masukkan username">
                        </div>
                    </div>

                    <div class="input-group space-y-1">
                        <label for="password"
                            class="block text-xs font-bold text-gray-500 uppercase tracking-wider transition-colors">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 transition-colors" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white transition-all duration-200 font-medium"
                                placeholder="Masukkan password">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full group relative flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg shadow-blue-500/30 transform transition-all active:scale-[0.98]">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200 transition-colors" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </span>
                        Masuk sebagai Admin
                    </button>
                </form>

                <div class="mt-8 relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white/80 backdrop-blur text-gray-500 font-medium rounded-full">Atau masuk
                            sebagai</span>
                    </div>
                </div>

                <form method="POST" class="mt-6">
                    <input type="hidden" name="guest_login" value="1">
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3.5 px-4 bg-white border-2 border-gray-100 hover:border-gray-200 hover:bg-gray-50 rounded-xl text-sm font-bold text-gray-700 transition-all duration-200 transform active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Tamu / Pengunjung
                    </button>
                </form>
            </div>

            <div class="px-8 py-4 bg-gray-50/50 border-t border-gray-100 text-center backdrop-blur-sm">
                <p class="text-xs font-medium text-gray-500">
                    &copy; <?= date('Y') ?> SMAPIJ RFID System. BY MARCO ENGINEERING
                </p>
            </div>
        </div>
    </div>
</body>

</html>