<?php
// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle ?? 'SIMPRAK'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">

    <nav class="bg-blue-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo / Brand -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white text-2xl font-bold">SIMPRAK</span>
                    </div>
                    <!-- Main Navigation -->
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                            $activeClass = 'bg-blue-700 text-white';
                            $inactiveClass = 'text-gray-200 hover:bg-blue-700 hover:text-white';
                        ?>
                            <a href="index.php"
                                class="<?php echo ($activePage == 'home') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Beranda</a>
                            <a href="katalog_praktikum.php"
                                class="<?php echo ($activePage == 'katalog') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Katalog
                                Praktikum</a>
                        </div>
                    </div>
                </div>
                <!-- Right-side buttons -->
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center space-x-4">
                        <?php if (isset($_SESSION['user'])): ?>
                        <a href="dashboard/<?php echo $_SESSION['user']['role']; ?>.php"
                            class="text-white font-medium hover:underline">
                            Dashboard
                        </a>
                        <a href="logout.php"
                            class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                            Logout
                        </a>
                        <?php else: ?>
                        <a href="login.php"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                            Login
                        </a>
                        <a href="register.php"
                            class="bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 px-4 rounded-md transition-colors duration-300">
                            Register
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 lg:p-8">