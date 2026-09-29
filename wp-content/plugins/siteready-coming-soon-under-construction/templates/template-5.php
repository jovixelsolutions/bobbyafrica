<?php
/**
 * Template Name: Under Construction Page (Template 5)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="uc5-wrapper">
    <div class="uc5-card">
        <!-- Top Icons -->
        <div class="uc5-icons">
           <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/icon-gear.png' ); ?>" alt="Gears">
           <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/icon-warning.png' ); ?>" alt="Warning">
        </div>

        <!-- Main content -->
        <div class="uc5-content">
            <?php 
            $sruc_sub_heading = $template_post_data['sruc_sub_heading'] ?? 'UNDER/CONSTRUCTION';
            $sruc_lines       = explode("/", $sruc_sub_heading);
            $sruc_first_line  = $template_post_data['sruc_sub_heading_first'] ?? 'UNDER';
            $sruc_second_line = $template_post_data['sruc_sub_heading_second'] ?? 'CONSTRUCTION';
            ?>
            
            <!-- Sub Heading (split into 2 parts) -->
            <h1 class="uc5-title"
                style="
                    font-family: <?php echo esc_attr( $template_post_data['sruc_sub_heading_first_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_sub_heading_first_size'] ?? '70px' ); ?>;
                    color: <?php echo esc_attr( $template_post_data['sruc_sub_heading_first_color'] ?? '#333333' ); ?>;
                ">
                <?php echo esc_html( $sruc_first_line ); ?> 
                <span class="uc5-highlight"
                    style="
                    font-family: <?php echo esc_attr( $template_post_data['sruc_sub_heading_second_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_sub_heading_second_size'] ?? '40px' ); ?>;
                    color: <?php echo esc_attr( $template_post_data['sruc_sub_heading_second_color'] ?? '#333333' ); ?>;
                ">
                    <?php echo esc_html( $sruc_second_line ); ?>
                </span>
            </h1>

            <!-- Main Heading -->
            <h2 class="uc5-subtitle"
                style="font-family: <?php echo esc_attr( $template_post_data['sruc_heading_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_heading_size'] ?? '25px' ); ?>;
                    color: <?php echo esc_attr( $template_post_data['sruc_heading_highlight_color'] ?? '#2d2d2d' ); ?>;
                ">
                <?php echo esc_html( $template_post_data['sruc_heading'] ?? '' ); ?>
            </h2>

            <!-- Description -->
            <p class="uc5-description"
                style="
                    font-family: <?php echo esc_attr( $template_post_data['sruc_description_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_description_size'] ?? '16px' ); ?>;
                    color: <?php echo esc_attr( $template_post_data['sruc_description_color'] ?? '#666666' ); ?>;
                ">
                <?php echo esc_html( $template_post_data['sruc_description'] ?? '' ); ?>
            </p>

            <!-- Contact -->
            <p class="uc5-contact"
                style="
                    font-family: <?php echo esc_attr( $template_post_data['sruc_short_description_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_short_description_size'] ?? '16px' ); ?>;
                    color: <?php echo esc_attr( $template_post_data['sruc_short_description_color'] ?? '#999999' ); ?>;
                ">
                <?php echo esc_html( $template_post_data['sruc_short_description'] ?? '' ); ?>
            </p>
        </div>
    </div>
</div>
