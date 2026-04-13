<?php

require_once dirname(__DIR__, 2) . '/Helpers/admin.php';

if (isset($conn) && $conn instanceof PDO) {
    lc_admin_sync_session_if_possible($conn);
    lc_admin_enforce_active_account($conn);
}

if (!function_exists('lc_shell_default_nav')) {
    function lc_public_asset(string $relativePath): string
    {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');
        $absolute = dirname(__DIR__, 3) . '/public/' . $clean;
        $version = is_file($absolute) ? (string)filemtime($absolute) : (string)time();

        if (defined('BASE_URL')) {
            return rtrim(BASE_URL, '/') . '/public/' . $clean . '?v=' . $version;
        }

        return '../../../public/' . $clean . '?v=' . $version;
    }

    function lc_shell_default_nav(): array
    {
        $nav = [
            'home' => [
                'href' => BASE_URL . 'app/Views/Home/dashboard.php',
                'icon' => 'fa-solid fa-home',
                'label' => 'Home',
            ],
            'explore' => [
                'href' => BASE_URL . 'app/Views/Explore/explore.php',
                'icon' => 'fa-solid fa-compass',
                'label' => 'Explore',
            ],
            'groups' => [
                'href' => BASE_URL . 'app/Views/Groups/groups.php',
                'icon' => 'fa-solid fa-people-group',
                'label' => 'Groups',
            ],
            'events' => [
                'href' => BASE_URL . 'app/Views/Events/events.php',
                'icon' => 'fa-solid fa-calendar-days',
                'label' => 'Events',
            ],
            'friends' => [
                'href' => BASE_URL . 'app/Views/Friends/AllFriends.php',
                'icon' => 'fa-solid fa-user-friends',
                'label' => 'Friends',
            ],
            'messages' => [
                'href' => BASE_URL . 'app/Views/Messages/message.php',
                'icon' => 'fa-solid fa-message',
                'label' => 'Messages',
            ],
            'report' => [
                'href' => BASE_URL . 'app/Views/Report/userReport.php',
                'icon' => 'fa-solid fa-chart-simple',
                'label' => 'Report',
            ],
        ];

        if (lc_current_user_is_admin()) {
            $nav['admin'] = [
                'href' => BASE_URL . 'app/Views/Admin/dashboard.php',
                'icon' => 'fa-solid fa-user-shield',
                'label' => 'Admin',
            ];
        }

        return $nav;
    }

    function lc_shell_render_sidebar(array $options = []): void
    {
        $active = (string)($options['active'] ?? '');
        $navItems = $options['nav_items'] ?? lc_shell_default_nav();
        $showCreatePost = array_key_exists('show_create_post', $options) ? (bool)$options['show_create_post'] : true;
        $createLabel = (string)($options['create_label'] ?? 'Create New Post');
        $createId = (string)($options['create_id'] ?? 'open-create-post');
        ?>
        <div class="sidebar app-shell-sidebar">
            <div class="logo-section">
                <i class="fa-solid fa-cube"></i>
                <span>LocalConnect</span>
            </div>

            <nav class="menu">
                <?php foreach ($navItems as $key => $item): ?>
                    <a
                        href="<?php echo htmlspecialchars((string)$item['href']); ?>"
                        class="item<?php echo $key === $active ? ' active' : ''; ?>"
                    >
                        <i class="<?php echo htmlspecialchars((string)$item['icon']); ?>"></i>
                        <?php echo htmlspecialchars((string)$item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($showCreatePost): ?>
                <button
                    class="btn-create js-open-create-post"
                    id="<?php echo htmlspecialchars($createId); ?>"
                    type="button"
                    data-open-create-post
                >
                    <i class="fa-solid fa-plus-circle"></i>
                    <span><?php echo htmlspecialchars($createLabel); ?></span>
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    function lc_shell_render_header(array $options = []): void
    {
        $searchHtml = $options['search_html'] ?? null;
        $searchPlaceholder = (string)($options['search_placeholder'] ?? 'Search for anything...');
        $searchInputId = (string)($options['search_input_id'] ?? '');
        $searchInputValue = (string)($options['search_input_value'] ?? '');
        $searchInputAttrs = trim((string)($options['search_input_attrs'] ?? ''));
        $notificationHtml = $options['notification_html'] ?? '<i class="fa-solid fa-bell"></i>';
        $notificationButtonId = trim((string)($options['notification_button_id'] ?? ''));
        $notificationButtonClass = trim((string)($options['notification_button_class'] ?? ''));
        $notificationButtonAttrs = trim((string)($options['notification_button_attrs'] ?? ''));
        $userName = (string)($options['user_name'] ?? 'User');
        $profileHref = (string)($options['profile_href'] ?? BASE_URL . 'app/Views/Profile/profile.php');
        $profileImage = (string)($options['profile_image'] ?? 'https://via.placeholder.com/150');
        $profileImageAttrs = trim((string)($options['profile_image_attrs'] ?? ''));
        $userNameClass = trim((string)($options['user_name_class'] ?? ''));
        $userNameAttrs = trim((string)($options['user_name_attrs'] ?? ''));
        $headerClass = trim((string)($options['header_class'] ?? ''));
        ?>
        <header class="app-shell-header<?php echo $headerClass !== '' ? ' ' . htmlspecialchars($headerClass) : ''; ?>">
            <button class="icon-btn menu-toggle" type="button" aria-label="Open menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="search-bar">
                <i class="fa-solid fa-search text"></i>
                <?php if ($searchHtml !== null): ?>
                    <?php echo $searchHtml; ?>
                <?php else: ?>
                    <input
                        type="text"
                        <?php echo $searchInputId !== '' ? 'id="' . htmlspecialchars($searchInputId) . '"' : ''; ?>
                        placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>"
                        value="<?php echo htmlspecialchars($searchInputValue); ?>"
                        <?php echo $searchInputAttrs; ?>
                    >
                <?php endif; ?>
            </div>

            <div class="header-actions">
                <button class="icon-btn search-toggle-btn" type="button" aria-label="Toggle search">
                    <i class="fa-solid fa-search"></i>
                </button>
                <button class="icon-btn theme-toggle" type="button" aria-label="Toggle theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <div
                    class="icon-btn<?php echo $notificationButtonClass !== '' ? ' ' . htmlspecialchars($notificationButtonClass) : ''; ?>"
                    <?php echo $notificationButtonId !== '' ? 'id="' . htmlspecialchars($notificationButtonId) . '"' : ''; ?>
                    style="position: relative;"
                    <?php echo $notificationButtonAttrs; ?>
                >
                    <?php echo $notificationHtml; ?>
                </div>
                <div class="user-profile">
                    <span
                        class="<?php echo htmlspecialchars(trim('app-shell-user-name ' . $userNameClass)); ?>"
                        <?php echo $userNameAttrs; ?>
                    >
                        <?php echo htmlspecialchars($userName); ?>
                    </span>
                    <a href="<?php echo htmlspecialchars($profileHref); ?>">
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" <?php echo $profileImageAttrs; ?>>
                    </a>
                </div>
            </div>
        </header>
        <?php
    }

    function lc_shell_render_overlay(): void
    {
        echo '<div class="overlay" data-shell-overlay></div>';
    }

    function lc_shell_render_user_bootstrap(int $userId, string $userName, string $avatarPath): void
    {
        ?>
        <script>
            const CURRENT_USER_ID = <?php echo (int)$userId; ?>;
            const CURRENT_USER_NAME = <?php echo json_encode($userName); ?>;
            const CURRENT_USER_AVATAR = <?php echo json_encode($avatarPath); ?>;
            window.LOCALCONNECT_CURRENT_USER = {
                id: CURRENT_USER_ID,
                name: CURRENT_USER_NAME,
                avatar: CURRENT_USER_AVATAR
            };
        </script>
        <?php
    }
}
