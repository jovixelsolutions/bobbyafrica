<?php
/**
 * Template Name: Under Construction Page (Template 3)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="uc-wrapper">
    <div class="uc-left">
        <div class="uc-illustration">
        </div>
    </div>

    <div class="uc-right">
        <div class="uc-content">
            <!-- Logo -->
          <div class="uc-logo">
                <img src="<?php echo esc_url( SRUC_PLUGIN_URL . 'assets/frontend/images/logo.png' ); ?>" alt="Logo" class="uc-logo-img">
                <!-- <span class="uc-logo-text">Logo</span> -->
          </div>


            <!-- Heading -->
             <!-- Sub Heading -->
            <h6 class="uc-sub-heading"
                style="
                    color: <?php echo esc_attr( $template_post_data['sruc_sub_heading_color'] ?? '#3f2b96' ); ?>;
                    font-family: <?php echo esc_attr( $template_post_data['sruc_sub_heading_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_sub_heading_size'] ?? '12px' ); ?>;
                ">
                <?php echo esc_html( $template_post_data['sruc_sub_heading'] ?? 'THIS PAGE IS' ); ?>
            </h6>

            <h2 class="uc-heading"
                style="
                    color: <?php echo esc_attr( $template_post_data['sruc_heading_color'] ?? '#3f2b96' ); ?>;
                    font-family: <?php echo esc_attr( $template_post_data['sruc_heading_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_heading_size'] ?? '24px' ); ?>;
                ">
                 <?php echo esc_html( $template_post_data['sruc_heading'] ?? 'UNDER CONSTRUCTION' ); ?>

            </h2>

            <!-- Description -->
            <p class="uc-text"
               style="
                    color: <?php echo esc_attr( $template_post_data['sruc_description_color'] ?? '#333333' ); ?>;
                    font-family: <?php echo esc_attr( $template_post_data['sruc_description_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_description_size'] ?? '14px' ); ?>;
               ">
                <?php echo esc_html( $template_post_data['sruc_description'] ?? 'Lorem ipsum...' ); ?>
            </p>

            <!-- Button -->
            <a href="<?php echo esc_url( $template_post_data['sruc_button_url'] ?? '#' ); ?>"
               class="uc-btn"
               style="
                    background-color: <?php echo esc_attr( $template_post_data['sruc_button_bg_color'] ?? '#f7b500' ); ?>;
                    color: <?php echo esc_attr( $template_post_data['sruc_button_text_color'] ?? '#ffffff' ); ?>;
                    font-family: <?php echo esc_attr( $template_post_data['sruc_button_font'] ?? 'inherit' ); ?>;
                    font-size: <?php echo esc_attr( $template_post_data['sruc_button_size'] ?? '16px' ); ?>;
                    padding: 12px 24px;
                    display: inline-block;
                    text-decoration: none;
                    border-radius: 4px;
               ">
                <?php echo esc_html( $template_post_data['sruc_button_text'] ?? 'LEARN MORE' ); ?>
            </a>
        </div>
    </div>
</div>
