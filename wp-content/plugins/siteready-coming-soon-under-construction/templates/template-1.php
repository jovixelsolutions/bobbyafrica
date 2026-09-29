<?php
/**
 * Template Name: Coming Soon Page (Template 1)
 */

if (!defined('ABSPATH')) {
    die;
}

?>

<div class="coming-soon-container">
    <div class="coming-soon-content">

        <!-- Heading -->
        <h1 class="coming-soon-title" style="color: <?php echo esc_attr($template_post_data['sruc_heading_color'] ?? '#000000'); ?>;
                font-family: <?php echo esc_attr($template_post_data['sruc_heading_font'] ?? 'inherit'); ?>;
                font-size: <?php echo esc_attr($template_post_data['sruc_heading_size'] ?? '32px'); ?>;">
            <?php echo esc_html($template_post_data['sruc_heading'] ?? 'COMING SOON'); ?>
        </h1>

        <!-- Sub Heading -->
        <p class="coming-soon-subtitle" style="
                color: <?php echo esc_attr($template_post_data['sruc_sub_heading_color'] ?? '#333333'); ?>;
                font-family: <?php echo esc_attr($template_post_data['sruc_sub_heading_font'] ?? 'inherit'); ?>;
                font-size: <?php echo esc_attr($template_post_data['sruc_sub_heading_size'] ?? '20px'); ?>;
           ">
            <?php echo esc_html($template_post_data['sruc_sub_heading'] ?? 'We Got Something New!'); ?>
        </p>

        <!-- Countdown Timer -->
        <div id="countdown"
            data-timestramp-total="<?php echo esc_attr($template_post_data['sruc_timestramp_total'] ?? '86400'); ?>"
            class="coming-soon-countdown" style="
                font-family: <?php echo esc_attr($template_post_data['sruc_timer_font'] ?? 'inherit'); ?>;
                font-size: <?php echo esc_attr($template_post_data['sruc_timer_size'] ?? '18px'); ?>;
             ">

            <div id="days" class="time-box">
                <span style="
                background-color: <?php echo esc_attr($template_post_data['sruc_timer_bg_color'] ?? '#000000'); ?>;
                color: <?php echo esc_attr($template_post_data['sruc_timer_text_color'] ?? '#ffffff'); ?>;
             " id="days-num">00</span><br>
                <span>Days</span>
            </div>
            <div id="hours" class="time-box">
                <span style="
                background-color: <?php echo esc_attr($template_post_data['sruc_timer_bg_color'] ?? '#000000'); ?>;
                color: <?php echo esc_attr($template_post_data['sruc_timer_text_color'] ?? '#ffffff'); ?>;
             " id="hours-num">00</span><br>
                <span>Hours</span>
            </div>
            <div id="minutes" class="time-box">
                <span style="
                background-color: <?php echo esc_attr($template_post_data['sruc_timer_bg_color'] ?? '#000000'); ?>;
                color: <?php echo esc_attr($template_post_data['sruc_timer_text_color'] ?? '#ffffff'); ?>;
             " id="minutes-num">00</span><br>
                <span>Minutes</span>
            </div>
            <div id="seconds" class="time-box">
                <span style="
                background-color: <?php echo esc_attr($template_post_data['sruc_timer_bg_color'] ?? '#000000'); ?>;
                color: <?php echo esc_attr($template_post_data['sruc_timer_text_color'] ?? '#ffffff'); ?>;
             " id="seconds-num">00</span><br>
                <span>Seconds</span>
            </div>
        </div>

        <!-- Short Description -->
        <p class="coming-soon-stay-tuned" style="
                color: <?php echo esc_attr($template_post_data['sruc_short_description_color'] ?? '#000000'); ?>;
                font-family: <?php echo esc_attr($template_post_data['sruc_short_description_font'] ?? 'inherit'); ?>;
                font-size: <?php echo esc_attr($template_post_data['sruc_short_description_size'] ?? '16px'); ?>;
           ">
            <?php echo esc_html($template_post_data['sruc_short_description'] ?? 'STAY TUNED!'); ?>
        </p>

        <!-- Description -->
        <p class="coming-soon-description" style="
                color: <?php echo esc_attr($template_post_data['sruc_description_color'] ?? '#000000'); ?>;
                font-family: <?php echo esc_attr($template_post_data['sruc_description_font'] ?? 'inherit'); ?>;
                font-size: <?php echo esc_attr($template_post_data['sruc_description_size'] ?? '16px'); ?>;
           ">
            <?php echo esc_html($template_post_data['sruc_description'] ?? 'Lorem ipsum...'); ?>
        </p>

    </div>
</div>