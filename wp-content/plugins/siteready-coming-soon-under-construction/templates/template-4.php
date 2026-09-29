<?php
/**
 * Template Name: Under Construction Page (Template 4)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="uc4-wrapper">
    
    <div class="uc4-header">
        <!-- Tool icons / illustrations -->
        <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/wrench-1.png' ); ?>" alt="Tool">
        <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/wrench-2.png' ); ?>" alt="Tool">
        <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/wrench-3.png' ); ?>" alt="Tool">
        <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/wrench-4.png' ); ?>" alt="Tool">
        <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/wrench-5.png' ); ?>" alt="Tool">

    </div>

    <div class="uc4-content">
        <?php 
        $sruc_heading_third = $template_post_data['sruc_heading_first'] ?? 'THIS SITE IS/UNDER/CONSTRUCTION';
        $sruc_first_line  = $template_post_data['sruc_heading_first'] ?? 'THIS SITE IS';
        $sruc_second_line = $template_post_data['sruc_heading_second'] ?? 'UNDER';
        $sruc_third_line  = $template_post_data['sruc_heading_third'] ?? 'CONSTRUCTION';
        ?>
        
        <!-- First Line -->
        <h2 class="uc4-subtitle"
            style="
                color: <?php echo esc_attr( $template_post_data['sruc_heading_first_color'] ?? '#333333' ); ?>;
                font-family: <?php echo esc_attr( $template_post_data['sruc_heading_first_font'] ?? 'inherit' ); ?>;
                font-size: <?php echo esc_attr( $template_post_data['sruc_heading_first_size'] ?? '36px' ); ?>;
            ">
            <?php echo esc_html( $sruc_first_line ); ?>
        </h2>

        <!-- Middle Highlighted Line -->
        <h1 class="uc4-title"
            style="
                color: <?php echo esc_attr( $template_post_data['sruc_heading_second_color'] ?? '#333333' ); ?>;
                font-family: <?php echo esc_attr( $template_post_data['sruc_heading_second_font'] ?? 'inherit' ); ?>;
                font-size: calc(<?php echo esc_attr( $template_post_data['sruc_heading_second_size'] ?? '36px' ); ?> * 1.2);
                font-weight: bold;
            ">
            <span class="uc4-highlight" style="
                color: <?php echo esc_attr( $template_post_data['sruc_heading_second_color'] ?? '#333333' ); ?>;
                font-family: <?php echo esc_attr( $template_post_data['sruc_heading_second_font'] ?? 'inherit' ); ?>;
                font-size: calc(<?php echo esc_attr( $template_post_data['sruc_heading_second_size'] ?? '36px' ); ?> * 1.2);
                font-weight: bold;
            "><?php echo esc_html( $sruc_second_line ); ?></span>
        </h1>

        <!-- Last Line -->
        <h2 class="uc4-subtitle"
            style="
                color: <?php echo esc_attr( $template_post_data['sruc_heading_third_color'] ?? '#333333' ); ?>;
                font-family: <?php echo esc_attr( $template_post_data['sruc_heading_third_font'] ?? 'inherit' ); ?>;
                font-size: <?php echo esc_attr( $template_post_data['sruc_heading_third_size'] ?? '36px' ); ?>;
            ">
            <?php echo esc_html( $sruc_third_line ); ?>
        </h2>
    </div>
</div>
