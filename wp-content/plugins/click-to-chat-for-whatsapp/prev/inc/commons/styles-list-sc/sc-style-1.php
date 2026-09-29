<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$o .= '<div class="ccw_plugin inline style1-sc sc_item '.$inline_issue.' " style=" '.$css.' " >';
$o .= '<button onclick="'.$img_click_link.'">'.esc_attr($a["val"]).'</button>';
$o .= '</div>';