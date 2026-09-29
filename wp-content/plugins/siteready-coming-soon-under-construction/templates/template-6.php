<?php
/**
 * Template Name: Under Construction Page (Template 6)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$logo               = $template_post_data['sruc_logo'] ?? 'COMPANY LOGO';

$sub_heading        = $template_post_data['sruc_sub_heading'] ?? 'Website is under construction';
$main_heading       = $template_post_data['sruc_heading'] ?? 'COMING SOON';
$description        = $template_post_data['sruc_description'] ?? '';

$banner_image       = SRUC_PLUGIN_URL . 'assets/frontend/images/template6.1.png';

$days               = $template_post_data['sruc_timestramp_days'] ?? '00';
$hours              = $template_post_data['sruc_timestramp_hours'] ?? '00';
$minutes            = $template_post_data['sruc_timestramp_minutes'] ?? '00';
$seconds            = $template_post_data['sruc_timestramp_seconds'] ?? '00';

$sub_color          = $template_post_data['sruc_sub_heading_color'] ?? '#000000';
$heading_color      = $template_post_data['sruc_heading_color'] ?? '#000000';
$desc_color         = $template_post_data['sruc_description_color'] ?? '#333333';

$timer_bg           = $template_post_data['sruc_timer_bg_color'] ?? '#000000';
$timer_text         = $template_post_data['sruc_timer_text_color'] ?? '#ffffff';

// Sizes
$sub_size           = $template_post_data['sruc_sub_heading_size'] ?? '30px';
$heading_size       = $template_post_data['sruc_heading_size'] ?? '70px';
$desc_size          = $template_post_data['sruc_description_size'] ?? '20px';
$timer_size         = $template_post_data['sruc_timer_size'] ?? '70px';
$timer_label_color  = $template_post_data['sruc_timer_label_color'] ?? '#333333';
$timer_label_size   = $template_post_data['sruc_timer_label_size'] ?? '16px';
?>

<section class="coming-soon uc-template-6">

    <?php if ( filter_var( $logo, FILTER_VALIDATE_URL ) ) : ?>
        <a href="#" class="site-logo">
            <img src="<?php echo esc_url( $logo ); ?>" alt="COMPANY LOGO">
        </a>
    <?php else : ?>
        <a href="#" class="site-logo" style="color: <?php echo esc_attr( $heading_color ); ?>;">
            <?php echo esc_html( 'COMPANY LOGO'); ?>
        </a>
    <?php endif; ?>

    <div class="main">

        <div class="image-area">
            <img src="<?php echo esc_url( $banner_image ); ?>" alt="Under Construction">
        </div>

        <div class="text-area">

            <p class="subtitle"
                style="color: <?php echo esc_attr( $sub_color ); ?>;
                       font-size: <?php echo esc_attr( $sub_size ); ?>;">
                <?php echo esc_html( $sub_heading ); ?>
            </p>

            <h1 style="color: <?php echo esc_attr( $heading_color ); ?>;
                       font-size: <?php echo esc_attr( $heading_size ); ?>;">
                <?php echo esc_html( $main_heading ); ?>
            </h1>

            <div class="timer coming-soon-countdown" 
                data-timestramp-total="<?php echo esc_attr( $template_post_data['sruc_timestramp_total'] ?? '0' ); ?>">

                <?php
                    $linear_gradient_color_timer_text = '';
                    if ( ! empty( $timer_text ) ){
                        $linear_gradient_color_timer_text = 'background: linear-gradient(180deg,' . esc_attr( $timer_text ) . '); -webkit-background-clip: text; -webkit-text-fill-color: transparent;';
                    }
                ?>

                <div style="background: <?php echo esc_attr( $timer_bg ); ?>;">
                    <span id="days-num"
                        style="<?php echo $linear_gradient_color_timer_text; ?> font-size: <?php echo esc_attr( $timer_size ); ?>;">
                        <?php echo esc_html( $days ); ?>
                    </span>
                    <small style="color: <?php echo esc_attr( $timer_label_color ); ?>; font-size: <?php echo esc_attr( $timer_label_size ); ?>;">
                        <?php echo esc_html( $timer_label_days ?? 'Days' ); ?>
                    </small>
                </div>

                <div style="background: <?php echo esc_attr( $timer_bg ); ?>;">
                    <span id="hours-num"
                        style="<?php echo $linear_gradient_color_timer_text; ?> font-size: <?php echo esc_attr( $timer_size ); ?>;">
                        <?php echo esc_html( $hours ); ?>
                    </span>
                    <small style="color: <?php echo esc_attr( $timer_label_color ); ?>; font-size: <?php echo esc_attr( $timer_label_size ); ?>;">
                        <?php echo esc_html( $timer_label_hours ?? 'Hours' ); ?>
                    </small>
                </div>

                <div style="background: <?php echo esc_attr( $timer_bg ); ?>;">
                    <span id="minutes-num"
                        style="<?php echo $linear_gradient_color_timer_text; ?> font-size: <?php echo esc_attr( $timer_size ); ?>;">
                        <?php echo esc_html( $minutes ); ?>
                    </span>
                    <small style="color: <?php echo esc_attr( $timer_label_color ); ?>; font-size: <?php echo esc_attr( $timer_label_size ); ?>;">
                        <?php echo esc_html( $timer_label_minutes ?? 'Minutes' ); ?>
                    </small>
                </div>

                <div style="background: <?php echo esc_attr( $timer_bg ); ?>;">
                    <span id="seconds-num"
                        style="<?php echo $linear_gradient_color_timer_text; ?> font-size: <?php echo esc_attr( $timer_size ); ?>;">
                        <?php echo esc_html( $seconds ); ?>
                    </span>
                    <small style="color: <?php echo esc_attr( $timer_label_color ); ?>; font-size: <?php echo esc_attr( $timer_label_size ); ?>;">
                        <?php echo esc_html( $timer_label_seconds ?? 'Seconds' ); ?>
                    </small>
                </div>

            </div>
            
            <div class="footer-text"
                style="color: <?php echo esc_attr( $desc_color ); ?>;
                       font-size: <?php echo esc_attr( $desc_size ); ?>;">
                <?php echo esc_html( $description ); ?>
            </div>

        </div>
    </div>

</section>
