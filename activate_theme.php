<?php
require __DIR__ . '/wp-load.php';

$theme_slug = 'bobbyafrica-marketplace-child';
$theme = wp_get_theme( $theme_slug );

if ( ! $theme->exists() ) {
    echo "THEME_MISSING\n";
    exit( 1 );
}

switch_theme( $theme->get_stylesheet() );

echo 'ACTIVE_THEME=' . wp_get_theme()->get_stylesheet() . PHP_EOL;

$im = imagecreatetruecolor( 1200, 900 );
$bg = imagecolorallocate( $im, 245, 247, 250 );
$teal = imagecolorallocate( $im, 15, 118, 110 );
$navy = imagecolorallocate( $im, 17, 38, 61 );
$white = imagecolorallocate( $im, 255, 255, 255 );
$panel = imagecolorallocate( $im, 241, 245, 249 );
$header = imagecolorallocate( $im, 31, 41, 55 );

imagefill( $im, 0, 0, $bg );
imagefilledrectangle( $im, 0, 0, 1200, 300, $teal );
imagefilledrectangle( $im, 0, 300, 1200, 900, $bg );
imagefilledrectangle( $im, 80, 120, 1120, 760, $panel );
imagefilledrectangle( $im, 120, 180, 440, 260, $white );
imagerectangle( $im, 80, 120, 1120, 760, $navy );
imagestring( $im, 5, 140, 206, 'BOBBYAFRICA', $header );
imagestring( $im, 3, 140, 320, 'Marketplace Child Theme', $navy );
imagestring( $im, 3, 140, 370, 'Responsive storefront foundation', $navy );
imagefilledrectangle( $im, 120, 430, 1080, 520, $teal );
imagestring( $im, 4, 160, 455, 'WooCommerce-ready marketplace styling', $white );

$screenshot_path = __DIR__ . '/wp-content/themes/bobbyafrica-marketplace-child/screenshot.png';
imagepng( $im, $screenshot_path );
imagedestroy( $im );

echo 'SCREENSHOT=' . $screenshot_path . PHP_EOL;
