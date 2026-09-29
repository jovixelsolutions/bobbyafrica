<?php
/**
 * Template Name: Under Construction Page (Template 2)
 */

if ( ! defined( 'ABSPATH' ) ) {
    die; 
}
?>

<div class="under-construction-container">
    <div class="under-construction-content">
        <div class="uc-board">
            <div class="uc-divider">
                <h1 class="uc-title"
                    style="
                        color: <?php echo esc_attr( $template_post_data['sruc_sub_heading_color'] ?? '#000000' ); ?>;
                        font-family: <?php echo esc_attr( $template_post_data['sruc_sub_heading_font'] ?? 'inherit' ); ?>;
                        font-size: <?php echo esc_attr( $template_post_data['sruc_sub_heading_size'] ?? '28px' ); ?>;
                    ">
                    <?php echo esc_html( $template_post_data['sruc_sub_heading'] ?? 'SORRY' ); ?>
                </h1>
                <h1 class="uc-title"
                    style="
                        color: <?php echo esc_attr( $template_post_data['sruc_heading_first_color'] ?? '#f7b500' ); ?>;
                        font-family: <?php echo esc_attr( $template_post_data['sruc_heading_first_font'] ?? 'inherit' ); ?>;
                        font-size: <?php echo esc_attr( $template_post_data['sruc_heading_first_size'] ?? '36px' ); ?>;
                    ">
                    <?php echo esc_html( $template_post_data['sruc_heading_first'] ?? 'UNDER' ); ?>
                </h1>
            </div>

            <h2 class="uc-heading"
                style="
                    color: <?php echo esc_attr( $template_post_data['sruc_heading_second_color'] ?? '#f7b500' ); ?>;
                    font-family: <?php echo esc_attr( $template_post_data['sruc_heading_second_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_heading_second_size'] ?? '36px' ); ?>;
                ">
                <?php echo esc_html( $template_post_data['sruc_heading_second'] ?? 'CONSTRUCTION' ); ?>
            </h2>
        </div>

        <div class="uc-warning">
            <div class="uc-bar"></div>
            <div class="uc-cones">
                <div class="cone"></div>
                <div class="cone"></div>
            </div>
        </div>

        <!-- Description -->
        <p class="uc-description"
           style="
                color: <?php echo esc_attr( $template_post_data['sruc_description_color'] ?? '#ffffff' ); ?>;
                font-family: <?php echo esc_attr( $template_post_data['sruc_description_font'] ?? 'inherit' ); ?>;
                font-size: <?php echo esc_attr( $template_post_data['sruc_description_size'] ?? '14px' ); ?>;
           ">
            <?php echo esc_html( $template_post_data['sruc_description'] ?? 'Designed by freepik.com' ); ?>
        </p>
    </div>
</div>
