<?php
require __DIR__ . '/wp-load.php';
$theme = wp_get_theme();

echo 'ACTIVE_THEME=' . $theme->get_stylesheet() . PHP_EOL;
echo 'PARENT=' . ($theme->parent() ? $theme->parent()->get('Name') : 'none') . PHP_EOL;

do_action('wp_enqueue_scripts');
$styles = wp_styles();
$handle_list = array_keys($styles->registered);
$matches = array_filter($handle_list, function ($handle) {
    return strpos($handle, 'bobbyafrica') !== false || strpos($handle, 'astra') !== false;
});

echo 'STYLE_HANDLES=' . implode(', ', $matches) . PHP_EOL;
