<?php

class SRUC_Admin_Menu
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_settings_menu'));
        add_action('admin_init', array($this, 'handle_template_meta_save'));
        add_filter('parent_file', array($this, 'add_nonce_to_menu_link'));
        add_action('wp_ajax_sruc_get_notice_dismiss', [$this, 'sruc_get_notice_dismiss']);

    }

    public function sruc_get_notice_dismiss()
    {
        delete_option('sruc_show_activation_popup');
        update_option('sruc_show_deactivation_popup', true);

        wp_send_json_success([
            "code" => 200,
            "msg" => "Activation popup preference saved successfully."
        ]);
    }

    private function format_meta_key_name($key)
    {
        $key = preg_replace('/^sruc_/', '', $key);

        // The underlying meta key is misspelled ("timestramp") but can't be renamed
        // without breaking already-saved data, so only the displayed label is corrected.
        $key = str_replace('timestramp', 'timestamp', $key);

        $parts = explode('_', $key);

        $parts = array_map('ucfirst', $parts);

        return implode(' ', $parts);
    }

    public function create_settings_menu()
    {
        add_menu_page(
            'Siteready Coming Soon Under Construction',
            'Under Construction',
            'manage_options',
            'sruc-settings',
            array($this, 'settings_page'),
            null,
            100
        );
    }

    public function add_nonce_to_menu_link($parent_file)
    {
        global $menu;

        $nonce = wp_create_nonce('sruc_settings_nonce');

        $template_id = get_option('sruc_active_template', '');

        foreach ($menu as $key => $item) {
            if (isset($item[2]) && $item[2] === 'sruc-settings') {
                $menu[$key][2] = add_query_arg(
                    array(
                        '_wpnonce' => $nonce,
                        'template_id' => $template_id,
                    ),
                    'admin.php?page=sruc-settings'
                );
                break;
            }
        }

        return $parent_file;
    }

    public function settings_page()
    {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
            wp_die(esc_html__('Security check failed.', 'siteready-coming-soon-under-construction'));
        }
        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'templates';
        $current_template = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
        ?>
        <div class='wrap'>
            <h1 class="plug-main-head"><?php esc_html_e('Siteready Coming Soon Under Construction', 'siteready-coming-soon-under-construction');
            ?></h1>

            <h2 class='nav-tab-wrapper'>
                <a href='<?php echo esc_url($this->sruc_admin_url(array(
                    'tab' => 'templates',
                    'template_id' => $current_template
                ))); ?>' class="nav-tab <?php echo $active_tab === 'templates' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Coming Soon Templates', 'siteready-coming-soon-under-construction');
                          ?></a>

                <a href="<?php echo esc_url($this->sruc_admin_url(array('tab' => 'settings','template_id' => $current_template))); ?>"
                    class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Settings', 'siteready-coming-soon-under-construction'); ?>
                </a>

                <a href="<?php echo esc_url($this->sruc_admin_url(array('tab' => 'theme_templates','template_id' => $current_template))); ?>"
                    class="nav-tab <?php echo $active_tab === 'theme_templates' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Website Templates', 'siteready-coming-soon-under-construction'); ?>
                </a>


            </h2>

            <?php
            if ($active_tab === 'templates') {
                $this->render_templates_tab();
            }
            elseif ($active_tab === 'settings') {
                $this->render_settings_tab();
            } 
            elseif ($active_tab === 'theme_templates') {
                $this->render_theme_templates_tab();
            }
            ?>
        </div>
        <?php
    }




    public function render_templates_tab()
    {
        $templates = get_posts(array(
            'post_type' => 'sruc_template',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC'
        ));

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
            wp_die(esc_html__('Security check failed.', 'siteready-coming-soon-under-construction'));
        }
        $current_template = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
        $active_template = get_option('sruc_active_template');

        echo '<div class="wrap">';
        echo '<div class="sruc-inner-wrap">';

        /** ---------------- Left Column ( Templates ) ---------------- */
        echo '<div>';
        echo '<div class="sruc-template-header">';
        echo '<h2>' . esc_html__('Templates', 'siteready-coming-soon-under-construction') . '</h2>';
        $enabled = get_option('sruc_enabled', 0);
        echo '<form method="post">';
        echo '<label class="sruc-switch">';
        echo '<input type="checkbox" name="sruc_enabled" 
                value="1" 
                ' . checked(1, $enabled, false) . ' 
                onchange="this.form.submit();" 
            >';
        echo '<span class="sruc-slider"></span>';
        echo '</label>';
        echo '<span style="margin-left:10px;">' . esc_html__('Enable Maintenance Mode', 'siteready-coming-soon-under-construction') . '</span>';
        echo '<input type="hidden" name="sruc_toggle_submit" value="1">';
        echo '</form>';
        echo '</div>';

        if ($templates) {
            echo '<div class="sruc-template-columns">';

            foreach ($templates as $template) {
                $image = get_the_post_thumbnail_url($template->ID, 'medium');
                $highlight = ($active_template == $template->ID)
                    ? 'border:2px solid #28a745; box-shadow:0 0 8px rgba(40,167,69,.5);'
                    : 'border:1px solid #ddd;';

                if($current_template == $template->ID) {
                    $highlight = ($current_template == $template->ID)
                    ? 'border:2px solid #3730a3; box-shadow:0 0 8px rgba(40, 63, 167, 0.5);'
                    : 'border:1px solid #ddd;';
                }

                echo '<div class="sruc-template-card" style="' . esc_attr($highlight) . '">';
                echo '<a class="sruc-template-card-link" href="' . esc_url(
                    $this->sruc_admin_url(array(
                        'tab' => 'templates',
                        'template_id' => $template->ID
                    ))
                ) . '" >';

                if ($image) {
                    echo '<img class="sruc-template-card-image" src="' . esc_url($image) . '"  />';
                } else {
                    echo '<div class="sruc-template-card-none-image">';
                    echo esc_html__('No Image', 'siteready-coming-soon-under-construction');
                    echo '</div>';
                }

                echo '<strong>' . esc_html($template->post_title) . '</strong>';
                echo '</a>';


                echo '<form method="post" style="margin-top:10px;">';
                echo '<input type="hidden" name="sruc_set_active_template" value="1">';
                echo '<input type="hidden" name="template_id" value="' . esc_attr($template->ID) . '">';
                if ($active_template == $template->ID) {
                    echo '<button type="submit" class="button button-primary" disabled>' . esc_html__('Active Template', 'siteready-coming-soon-under-construction') . '</button>';
                } else {
                    echo '<button type="submit" class="button button-primary">' . esc_html__('Set as Active', 'siteready-coming-soon-under-construction') . '</button>';
                }
                echo '</form>';

                echo '</div>';
            }

            echo '</div>';
        } else {
            echo '<p>' . esc_html__('No templates found.', 'siteready-coming-soon-under-construction') . '</p>';
        }

        echo '</div>';

        /** ---------------- Right Column ( Settings ) ---------------- */

        echo '<div class="sruc-template-settings">';
        echo '<div class="sruc-template-settings-inner">';
        echo '<h2>' . esc_html__('Template Settings', 'siteready-coming-soon-under-construction') . '</h2>';

        // Show active template notice
        if ($active_template) {
            $active_title = get_the_title($active_template);
            echo '<div class="sruc-template-active-notice">';
            echo '<strong>' . esc_html__('Active Template:', 'siteready-coming-soon-under-construction') . '</strong> ' . esc_html($active_title);
            echo '</div>';
        } else {
            echo '<div class="sruc-template-inactive-notice">';
            echo esc_html__('No template is currently active.', 'siteready-coming-soon-under-construction');
            echo '</div>';
        }

        // Preview link
        if ($current_template) {
            $nonce = wp_create_nonce('sruc_settings_nonce');
            $preview_url = add_query_arg(array(
                'sruc_preview' => 1,
                'template_id' => $current_template,
                '_wpnonce' => $nonce,
            ), home_url('/'));

            echo '<div class="sruc-preview-link  d-flex justify-content-end" >';
            echo '<a href="' . esc_url($preview_url) . '" target="_blank" class="button button-secondary">';
            echo esc_html__('Preview Template', 'siteready-coming-soon-under-construction');
            echo '</a>';
            echo '</div>';
        }

        echo '<ul class="nav nav-tabs" id="SRUCTab" role="tablist">
            <li class="nav-item" role="presentation">
            <button class="nav-link active" id="text-tab" data-bs-toggle="tab" data-bs-target="#text" type="button" role="tab">Text</button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="font-tab" data-bs-toggle="tab" data-bs-target="#font" type="button" role="tab">Font</button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="color-tab" data-bs-toggle="tab" data-bs-target="#color" type="button" role="tab">Color</button>
            </li>
            <li class="nav-item" role="presentation">
            <button class="nav-link" id="custom-css-tab" data-bs-toggle="tab" data-bs-target="#custom-css" type="button" role="tab">Custom CSS</button>
            </li>
        </ul>';

        echo '<div class="tab-content" id="SRUCTabContent">';
        $this->render_text_tab($current_template);
        echo '<div class="tab-pane" id="font" role="tabpanel">';
        $this->render_font_tab($current_template);
        echo '</div>';
        echo '<div class="tab-pane" id="color" role="tabpanel">';
        $this->render_color_tab($current_template);
        echo '</div>';
        echo '<div class="tab-pane" id="custom-css" role="tabpanel">';
        $this->render_custom_css_tab($current_template);
        echo '</div>
            </div>
            </div>';

        echo '</div>';
        echo '</div>';

        echo '</div>';

        echo '</div>';

    }

    public function render_theme_templates_tab()
    {

        $file = SRUC_PLUGIN_DIR . 'admin/themes-page/themes-page.php';
        if (file_exists($file)) {
            require_once $file;
            sruc_render_themes_page();
        }

    }

    private function render_text_tab($current_template)
    {
        echo '<div class="tab-pane show active" id="text" role="tabpanel">';

        if ($current_template) {
            $meta_keys = get_post_custom_keys($current_template);
            $template_key = get_post_meta($current_template, 'sruc_template_key', true);

            if ($template_key == 'sruc_template_6' && !in_array('sruc_logo', $meta_keys, true)) {
                $meta_keys[] = 'sruc_logo';
            }



            echo '<form method="post">';
            echo '<input type="hidden" name="sruc_update_template" value="1">';
            echo '<input type="hidden" name="template_id" value="' . esc_attr($current_template) . '">';

            echo '<table class="form-table">';
            if ($meta_keys) {

                foreach ($meta_keys as $key) {
                    if (
                        strpos($key, 'sruc_') === 0
                        && strpos($key, '_color') === false
                        && strpos($key, '_font') === false
                        && strpos($key, '_size') === false
                        && $key !== 'sruc_template_key'
                        && $key !== 'sruc_timestramp_total'
                        && $key !== 'sruc_update_template'
                        && $key !== 'sruc_custom_css'
                    ) {

                        if ($key == 'sruc_logo') {
                            ?>
                            <tr>
                                <th scope="row">
                                    <label for="<?php echo esc_attr($key); ?>">
                                        <?php
                                        $label = $this->format_meta_key_name($key);
                                        $value = get_post_meta($current_template, $key, true);
                                        echo esc_html($label); ?>
                                    </label>
                                </th>
                                <td>
                                    <div class="sruc-logo-field">
                                        <img src="<?php echo esc_url($value); ?>" class="sruc-logo-preview"
                                            style="max-width:150px;<?php echo empty($value) ? 'display:none;' : ''; ?>" />
                                        <input type="text" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>"
                                            class="regular-text sruc-logo-url" />
                                        <br>
                                        <button type="button" class="button sruc-logo-upload mt-2">
                                            <?php esc_html_e('Upload/Select Image', 'siteready-coming-soon-under-construction'); ?>
                                        </button>
                                        <button type="button" class="button sruc-logo-remove mt-2"
                                            style="<?php echo empty($value) ? 'display:none;' : ''; ?>">
                                            <?php esc_html_e('Remove', 'siteready-coming-soon-under-construction'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        } else {
                            $value = get_post_meta($current_template, $key, true);
                            $label = $this->format_meta_key_name($key);
                            echo '<tr>';
                            echo '<th scope="row"><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
                            echo '<td><input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" /></td>';
                            echo '</tr>';
                        }



                    }
                }
            } else {
                echo '<tr><td colspan="2"><em>' . esc_html__('No custom settings found for this template.', 'siteready-coming-soon-under-construction') . '</em></td></tr>';
            }
            echo '</table>';

            submit_button(esc_html__('Save Template Settings', 'siteready-coming-soon-under-construction'));
            echo '</form>';
        } else {
            echo '<p>' . esc_html__('Select a template to edit its settings.', 'siteready-coming-soon-under-construction') . '</p>';
        }
        echo '</div>';
    }

    private function render_font_tab($current_template)
    {

        echo '<div class="tab-pane" id="font" role="tabpanel">';

        if ($current_template) {

            $meta_keys = get_post_custom_keys($current_template);

            $available_fonts = array(
                '"Arial", sans-serif' => 'Arial',
                '"Courier New", monospace' => 'Courier New',
                '"Times New Roman", serif' => 'Times New Roman',
            );

            echo '<form method="post">';
            echo '<input type="hidden" name="sruc_update_template" value="1">';
            echo '<input type="hidden" name="template_id" value="' . esc_attr($current_template) . '">';

            echo '<table class="form-table">';
            if ($meta_keys) {
                foreach ($meta_keys as $key) {
                    // Only font and size fields
                    if (strpos($key, '_font') !== false || strpos($key, '_size') !== false) {
                        $value = get_post_meta($current_template, $key, true);
                        $label = $this->format_meta_key_name($key);

                        echo '<tr>';
                        echo '<th scope="row"><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
                        echo '<td>';

                        if (strpos($key, '_font') !== false) {

                            echo '<select name="' . esc_attr($key) . '" class="sruc-regular-select">';
                            foreach ($available_fonts as $font_value => $font_label) {
                                $selected = ($value === $font_value) ? 'selected' : '';
                                echo '<option value="' . esc_attr($font_value) . '" ' . esc_attr($selected) . '>' . esc_html($font_label) . '</option>';
                            }
                            echo '</select>';
                        } else {

                            echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" />';
                        }

                        echo '</td>';
                        echo '</tr>';
                    }
                }
            }
            echo '</table>';

            submit_button(esc_html__('Save Font Settings', 'siteready-coming-soon-under-construction'));
            echo '</form>';
        } else {
            echo '<p>' . esc_html__('Select a template to edit its settings.', 'siteready-coming-soon-under-construction') . '</p>';
        }

        echo '</div>';
    }

    private function render_color_tab($current_template)
    {
        echo '<div class="tab-pane" id="color" role="tabpanel">';

        if ($current_template) {
            $meta_keys = get_post_custom_keys($current_template);

            echo '<form method="post">';
            echo '<input type="hidden" name="sruc_update_template" value="1">';
            echo '<input type="hidden" name="template_id" value="' . esc_attr($current_template) . '">';

            echo '<table class="form-table">';
            if ($meta_keys) {
                foreach ($meta_keys as $key) {
                    // Only color fields
                    if (strpos($key, '_color') !== false) {
                        $value = get_post_meta($current_template, $key, true);
                        $label = $this->format_meta_key_name($key);

                        echo '<tr>';
                        echo '<th scope="row"><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
                        echo '<td>';
                        echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="sruc-color-field" />';
                        echo '</td>';
                        echo '</tr>';
                    }
                }
            }
            echo '</table>';

            submit_button(esc_html__('Save Color Settings', 'siteready-coming-soon-under-construction'));
            echo '</form>';
        } else {
            echo '<p>' . esc_html__('Select a template to edit its settings.', 'siteready-coming-soon-under-construction') . '</p>';
        }

        echo '</div>';
    }

    private function render_custom_css_tab($current_template)
    {
        echo '<div class="tab-pane" id="custom-css" role="tabpanel">';

        if ($current_template) {
            echo '<form method="post">';
            echo '<input type="hidden" name="sruc_update_template" value="1">';
            echo '<input type="hidden" name="template_id" value="' . esc_attr($current_template) . '">';

            $custom_css = get_post_meta($current_template, 'sruc_custom_css', true);

            echo '<table class="form-table">';
            echo '<tr>';
            echo '<th scope="row"><label for="sruc_custom_css">' . esc_html__('Custom CSS', 'siteready-coming-soon-under-construction') . '</label></th>';
            echo '<td>';
            echo '<textarea name="sruc_custom_css" id="sruc_custom_css" rows="10" cols="50" class="large-text code" placeholder=".coming-soon-subtitle { color: #33dd9d !important; }&#10;.coming-soon-title { font-size: 48px !important; }">' . esc_textarea($custom_css) . '</textarea>';
            echo '<p class="description">' . esc_html__('Add custom CSS to override default styles. Use !important to ensure your styles take precedence.', 'siteready-coming-soon-under-construction') . '</p>';
            echo '<p class="description"><strong>' . esc_html__('Examples:', 'siteready-coming-soon-under-construction') . '</strong></p>';
            echo '<ul style="margin-left: 20px;">';
            echo '<li><code>.coming-soon-subtitle { color: #33dd9d !important; }</code></li>';
            echo '<li><code>.coming-soon-title { font-size: 48px !important; }</code></li>';
            echo '<li><code>body { background: linear-gradient(45deg, #ff6b6b, #4ecdc4) !important; }</code></li>';
            echo '</ul>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';

            submit_button(esc_html__('Save Custom CSS', 'siteready-coming-soon-under-construction'));
            echo '</form>';
        } else {
            echo '<p>' . esc_html__('Select a template to edit its custom CSS.', 'siteready-coming-soon-under-construction') . '</p>';
        }

        echo '</div>';
    }

    public function render_settings_tab()
    {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
            wp_die(esc_html__('Security check failed.', 'siteready-coming-soon-under-construction'));
        }

        $enabled = get_option('sruc_enabled', 0);
        $auto_time = get_option('sruc_auto_disable_time', '');

        echo '<div class="wrap">';
        echo '<div class="">';

        /** ---------------- RIGHT SIDE (MAIN SETTINGS UI) ---------------- */
        echo '<div class="sruc-template-settings">';
        echo '<div class="sruc-template-settings-inner sruc-template-settings-inner-width mt-0">';

        echo '<h2>' . esc_html__('General Settings', 'siteready-coming-soon-under-construction') . '</h2>';
        echo '<p>' . esc_html__('Configure global settings for maintenance mode.', 'siteready-coming-soon-under-construction') . '</p>';

        echo '<form method="post">';
        wp_nonce_field('sruc_settings_nonce');

        echo '<table class="form-table">';

        /** Auto Disable Time */
        echo '<tr>';
        echo '<th>' . esc_html__('Auto Disable Time', 'siteready-coming-soon-under-construction') . '</th>';
        echo '<td>';
        echo '<input type="datetime-local" name="sruc_auto_disable_time" value="' . esc_attr($auto_time) . '" class="regular-text">';
        echo '<p class="description">' . esc_html__('Set a date & time to automatically disable maintenance mode.', 'siteready-coming-soon-under-construction') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '</table>';

        echo '<input type="hidden" name="sruc_save_settings" value="1">';
        submit_button(esc_html__('Save Settings', 'siteready-coming-soon-under-construction'));

        echo '</form>';

        echo '</div>'; // inner
        echo '</div>'; // right panel

        echo '</div>'; // inner wrap
        echo '</div>'; // wrap
    }

    public function handle_template_meta_save()
    {


        if (isset($_POST['sruc_enabled']) || isset($_POST['sruc_toggle_submit'])) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
                wp_die(esc_html__('Security check failed.', 'siteready-coming-soon-under-construction'));
            }

            $enabled = isset($_POST['sruc_enabled']) ? 1 : 0;
            update_option('sruc_enabled', $enabled);
        }

        if (isset($_POST['sruc_update_template']) && current_user_can('manage_options') && isset($_POST['template_id'])) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
                wp_die(esc_html__('Security check failed.', 'siteready-coming-soon-under-construction'));
            }

            $template_id = intval($_POST['template_id']);

            $has_countdown_fields = isset($_POST['sruc_timestramp_days'])
                || isset($_POST['sruc_timestramp_hours'])
                || isset($_POST['sruc_timestramp_minutes'])
                || isset($_POST['sruc_timestramp_seconds']);

            foreach ($_POST as $key => $value) {
                if (strpos($key, 'sruc_') === 0) {
                    update_post_meta($template_id, $key, sanitize_text_field($value));
                }
            }

            // Only recalculate the countdown end time when it was actually submitted
            // (i.e. the Text tab), so saving Font/Color/Custom CSS doesn't reset it.
            if ($has_countdown_fields) {
                $days = isset($_POST['sruc_timestramp_days']) ? intval($_POST['sruc_timestramp_days']) : 0;
                $hours = isset($_POST['sruc_timestramp_hours']) ? intval($_POST['sruc_timestramp_hours']) : 0;
                $minutes = isset($_POST['sruc_timestramp_minutes']) ? intval($_POST['sruc_timestramp_minutes']) : 0;
                $seconds = isset($_POST['sruc_timestramp_seconds']) ? intval($_POST['sruc_timestramp_seconds']) : 0;

                $total_seconds = ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds;
                $end_timestamp = time() + $total_seconds;

                update_post_meta($template_id, 'sruc_timestramp_total', $end_timestamp);
            }

            // wp_safe_redirect( admin_url( 'admin.php?page=sruc-settings&tab=templates&template_id=' . $template_id . '&updated=true' ) );
            wp_safe_redirect($this->sruc_admin_url(array(
                'tab' => 'templates',
                'template_id' => $template_id,
                'updated' => 'true'
            )));
            exit;
        }
        if (isset($_POST['sruc_set_active_template']) && current_user_can('manage_options') && isset($_POST['template_id'])) {
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sruc_settings_nonce')) {
                wp_die(esc_html__('Security check failed.', 'siteready-coming-soon-under-construction'));
            }

            $template_id = intval($_POST['template_id']);
            update_option('sruc_active_template', $template_id);

            wp_safe_redirect($this->sruc_admin_url(array(
                'tab' => 'templates',
                'template_id' => $template_id,
            )));

            // wp_safe_redirect( admin_url( 'admin.php?page=sruc-settings&tab=templates&template_id=' . $template_id  ) );
            exit;
        }

        if (isset($_POST['sruc_save_settings']) && current_user_can('manage_options')) {

            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'sruc_settings_nonce')) {
                wp_die('Security check failed');
            }

            $time = isset($_POST['sruc_auto_disable_time']) 
                ? sanitize_text_field($_POST['sruc_auto_disable_time']) 
                : '';

            update_option('sruc_auto_disable_time', $time);
        }
    }

    public function sruc_admin_url($args = array())
    {

        $base_url = admin_url('admin.php');
        $args = wp_parse_args($args, array(
            'page' => 'sruc-settings',
        ));

        $nonce = wp_create_nonce('sruc_settings_nonce');
        $args['_wpnonce'] = $nonce;
        return add_query_arg($args, $base_url);
    }
}