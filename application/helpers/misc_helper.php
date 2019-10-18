<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * CHeck missing key from the main english language
 * @param  string $language language to check
 * @return void
 */
function check_missing_language_strings($language)
{
    $langs = [];
    $CI    = & get_instance();
    $CI->lang->load('english_lang', 'english');
    $english = $CI->lang->language;
    $langs[] = [
        'english' => $english,
    ];
    $original      = $english;
    $keys_original = [];
    foreach ($original as $k => $val) {
        $keys_original[$k] = true;
    }
    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];
    $CI->lang->load($language . '_lang', $language);
    $$language = $CI->lang->language;
    $langs[]   = [
        $language => $$language,
    ];
    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];
    $missing_keys        = [];
    for ($i = 0; $i < count($langs); $i++) {
        foreach ($langs[$i] as $lang => $data) {
            if ($lang != 'english') {
                $keys_current = [];
                foreach ($data as $k => $v) {
                    $keys_current[$k] = true;
                }
                foreach ($keys_original as $k_original => $val_original) {
                    if (!array_key_exists($k_original, $keys_current)) {
                        $keys_missing = true;
                        array_push($missing_keys, $k_original);
                        echo '<b>Missing language key</b> from language:' . $lang . ' - <b>key</b>:' . $k_original . '<br />';
                    }
                }
            }
        }
    }
    if (isset($keys_missing)) {
        echo '<br />--<br />Language keys missing please create <a href="https://help.perfexcrm.com/overwrite-translation-text/" target="_blank">custom_lang.php</a> and add the keys listed above.';
        echo '<br /> Here is how you should add the keys (You can just copy paste this text above and add your translations)<br /><br />';
        foreach ($missing_keys as $key) {
            echo '$lang[\'' . $key . '\'] = \'Add your translation\';<br />';
        }
    } else {
        echo '<h1>No Missing Language Keys Found</h1>';
    }
    die;
}

/**
 * Return locale for media usafe plugin
 * @param  string $locale current locale
 * @return string
 */
function get_media_locale($locale)
{
    $lng = $locale;
    if ($lng == 'ja') {
        $lng = 'jp';
    } elseif ($lng == 'pt') {
        $lng = 'pt_BR';
    } elseif ($lng == 'ug') {
        $lng = 'ug_CN';
    } elseif ($lng == 'zh') {
        $lng = 'zh_TW';
    }

    return $lng;
}
/**
 * Replace google drive links with actual a tag
 * @param  string $text
 * @return string
 */
function handle_google_drive_links_in_text($text)
{
    $pattern = '#\bhttps?://drive.google.com[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
    preg_match_all($pattern, $text, $matchGoogleDriveLinks);

    if (isset($matchGoogleDriveLinks[0]) && is_array($matchGoogleDriveLinks[0])) {
        foreach ($matchGoogleDriveLinks[0] as $driveLink) {
            $link = '<a href="' . $driveLink . '">' . $driveLink . '</a>';
            $text = str_replace($driveLink, $link, $text);
            $text = str_replace('<' . $link . '>', $link, $text);
        }
    }

    return $text;
}
/**
 * Get system favourite colors
 * @return array
 */
function get_system_favourite_colors()
{
    // don't delete any of these colors are used all over the system
    $colors = [
        '#28B8DA',
        '#03a9f4',
        '#c53da9',
        '#757575',
        '#8e24aa',
        '#d81b60',
        '#0288d1',
        '#7cb342',
        '#fb8c00',
        '#84C529',
        '#fb3b3b',
    ];

    $colors = do_action('system_favourite_colors', $colors);

    return $colors;
}
/**
 * Get goal types for the goals feature
 * @return array
 */
function get_goal_types()
{
    $types = [
        [
            'key'      => 1,
            'lang_key' => 'goal_type_total_income',
            'subtext'  => 'goal_type_income_subtext',
        ],
        [
            'key'      => 2,
            'lang_key' => 'goal_type_convert_leads',
        ],
        [
            'key'      => 3,
            'lang_key' => 'goal_type_increase_customers_without_leads_conversions',
            'subtext'  => 'goal_type_increase_customers_without_leads_conversions_subtext',
        ],
        [
            'key'      => 4,
            'lang_key' => 'goal_type_increase_customers_with_leads_conversions',
            'subtext'  => 'goal_type_increase_customers_with_leads_conversions_subtext',
        ],
        [
            'key'      => 5,
            'lang_key' => 'goal_type_make_contracts_by_type_calc_database',
            'subtext'  => 'goal_type_make_contracts_by_type_calc_database_subtext',
        ],
        [
            'key'      => 7,
            'lang_key' => 'goal_type_make_contracts_by_type_calc_date',
            'subtext'  => 'goal_type_make_contracts_by_type_calc_date_subtext',
        ],
        [
            'key'      => 6,
            'lang_key' => 'goal_type_total_estimates_converted',
            'subtext'  => 'goal_type_total_estimates_converted_subtext',
        ],
    ];

    return do_action('get_goal_types', $types);
}
/**
 * Translate goal type based on passed key
 * @param  mixed $key
 * @return string
 */
function format_goal_type($key)
{
    foreach (get_goal_types() as $type) {
        if ($type['key'] == $key) {
            return _l($type['lang_key']);
        }
    }

    return $type;
}

function process_digital_signature_image($partBase64, $path)
{
    if (empty($partBase64)) {
        return false;
    }

    $filename = 'signature.png';

    $decoded_image = base64_decode($partBase64);

    $retval = false;
    _maybe_create_upload_path($path);

    $path = rtrim($path, '/') . '/' . unique_filename($path, $filename);

    $fp = fopen($path, 'w+');

    if (fwrite($fp, $decoded_image)) {
        $retval                                 = true;
        $GLOBALS['processed_digital_signature'] = $filename;
    }

    fclose($fp);

    return $retval;
}

/**
 * Used for estimate and proposal acceptance info array
 * @param  boolean $empty should the array values be empty or taken from $_POST
 * @return array
 */
function get_acceptance_info_array($empty = false)
{
    $CI        = &get_instance();
    $signature = null;

    if (isset($GLOBALS['processed_digital_signature'])) {
        $signature = $GLOBALS['processed_digital_signature'];
        unset($GLOBALS['processed_digital_signature']);
    }

    $data = [
        'signature'            => $signature,
        'acceptance_firstname' => !$empty ? $CI->input->post('acceptance_firstname') : null,
        'acceptance_lastname'  => !$empty ? $CI->input->post('acceptance_lastname') : null,
        'acceptance_email'     => !$empty ? $CI->input->post('acceptance_email'): null,
        'acceptance_date'      => !$empty ? date('Y-m-d H:i:s') : null,
        'acceptance_ip'        => !$empty ? $CI->input->ip_address() : null,
        'acceptance_ip'        => !$empty ? $CI->input->ip_address() : null,
    ];

    $hook_data = do_action('acceptance_info_array', ['data' => $data, 'empty' => $empty]);

    return $hook_data['data'];
}
/**
 * Get available locaes predefined for the system
 * If you add a language and the locale do not exist in this array you can use action hook to add new locale
 * @return array
 */
function get_locales()
{
    $locales = [
        'Arabic'      => 'ar',
        'Bulgarian'   => 'bg',
        'Catalan'     => 'ca',
        'Czech'       => 'cs',
        'Danish'      => 'da',
        'Albanian'    => 'sq',
        'German'      => 'de',
        'Deutsch'     => 'de',
        'Dutch'       => 'nl',
        'Greek'       => 'el',
        'English'     => 'en',
        'Finland'     => 'fi',
        'Spanish'     => 'es',
        'Persian'     => 'fa',
        'Finnish'     => 'fi',
        'French'      => 'fr',
        'Hebrew'      => 'he',
        'Hindi'       => 'hi',
        'Indonesian'  => 'id',
        'Hindi'       => 'hi',
        'Croatian'    => 'hr',
        'Hungarian'   => 'hu',
        'Icelandic'   => 'is',
        'Italian'     => 'it',
        'Japanese'    => 'ja',
        'Korean'      => 'ko',
        'Lithuanian'  => 'lt',
        'Latvian'     => 'lv',
        'Norwegian'   => 'nb',
        'Netherlands' => 'nl',
        'Polish'      => 'pl',
        'Portuguese'  => 'pt',
        'Romanian'    => 'ro',
        'Russian'     => 'ru',
        'Slovak'      => 'sk',
        'Slovenian'   => 'sl',
        'Serbian'     => 'sr',
        'Swedish'     => 'sv',
        'Thai'        => 'th',
        'Turkish'     => 'tr',
        'Ukrainian'   => 'uk',
        'Vietnamese'  => 'vi',
    ];

    $locales = do_action('before_get_locales', $locales);

    return $locales;
}
/**
 * Tinymce language set can be complicated and this function will scan the available languages
 * Will return lang filename in the tinymce plugins folder if found or if $locale is en will return just en
 * @param  [type] $locale [description]
 * @return [type]         [description]
 */
function get_tinymce_language($locale)
{
    $av_lang = list_files(FCPATH . 'assets/plugins/tinymce/langs/');
    $_lang   = '';
    if ($locale == 'en') {
        return $_lang;
    }

    if ($locale == 'hi') {
        return 'hi_IN';
    } elseif ($locale == 'he') {
        return 'he_IL';
    } elseif ($locale == 'sv') {
        return 'sv_SE';
    }

    foreach ($av_lang as $lang) {
        $_temp_lang = explode('.', $lang);
        if ($locale == $_temp_lang[0]) {
            return $locale;
        } elseif ($locale . '_' . strtoupper($locale) == $_temp_lang[0]) {
            return $locale . '_' . strtoupper($locale);
        }
    }

    return $_lang;
}

/**
 * All permissions available in the app with conditions
 * @return array
 */
function get_permission_conditions()
{
    return do_action('staff_permissions_conditions', [
        'contracts' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'leads' => [
            'view'        => true,
            'view_own'    => false,
            'edit'        => false,
            'create'      => false,
            'delete'      => true,
            'help'        => _l('help_leads_permission_view'),
            'help_create' => _l('help_leads_create_permission'),
            'help_edit'   => _l('help_leads_edit_permission'),
        ],
        'tasks' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
            'help'     => _l('help_tasks_permissions'),
        ],
        'checklist_templates' => [
            'view'     => false,
            'view_own' => false,
            'edit'     => false,
            'create'   => true,
            'delete'   => true,
        ],
        'reports' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => false,
            'create'   => false,
            'delete'   => false,
        ],
        'settings' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => false,
            'delete'   => false,
        ],
        'projects' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
            'help'     => _l('help_project_permissions'),
        ],
        'subscriptions' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'surveys' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'staff' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'customers' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'email_templates' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => false,
            'delete'   => false,
        ],
        'roles' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'expenses' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'bulk_pdf_exporter' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => false,
            'create'   => false,
            'delete'   => false,
        ],
        'goals' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'knowledge_base' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'proposals' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'estimates' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'payments' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'invoices' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'credit_notes' => [
            'view'     => true,
            'view_own' => true,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
        'items' => [
            'view'     => true,
            'view_own' => false,
            'edit'     => true,
            'create'   => true,
            'delete'   => true,
        ],
    ]);
}

/**
 * Feature that will render all JS necessary data in admin head
 * @return void
 */
function render_admin_js_variables()
{
    $date_format = get_option('dateformat');
    $date_format = explode('|', $date_format);
    $date_format = $date_format[0];
    $CI          = &get_instance();

    $js_vars = [
        'site_url'                                    => site_url(),
        'admin_url'                                   => admin_url(),
        'max_php_ini_upload_size_bytes'               => file_upload_max_size(),
        'google_api'                                  => '',
        'calendarIDs'                                 => '',
        'is_admin'                                    => is_admin(),
        'is_staff_member'                             => is_staff_member(),
        'has_permission_tasks_checklist_items_delete' => has_permission('checklist_templates', '', 'delete'),
        'app_language'                                => get_staff_default_language(),
        'app_is_mobile'                               => is_mobile(),
        'app_user_browser'                            => strtolower($CI->agent->browser()),
        'app_date_format'                             => $date_format,
        'app_decimal_places'                          => get_decimal_places(),
        'app_scroll_responsive_tables'                => get_option('scroll_responsive_tables'),
        'app_company_is_required'                     => get_option('company_is_required'),
        'app_default_view_calendar'                   => get_option('default_view_calendar'),
        'app_maximum_allowed_ticket_attachments'      => get_option('maximum_allowed_ticket_attachments'),
        'app_show_setup_menu_item_only_on_hover'      => get_option('show_setup_menu_item_only_on_hover'),
        'app_calendar_events_limit'                   => get_option('calendar_events_limit'),
        'app_tables_pagination_limit'                 => get_option('tables_pagination_limit'),
        'app_newsfeed_maximum_files_upload'           => get_option('newsfeed_maximum_files_upload'),
        'app_time_format'                             => get_option('time_format'),
        'app_decimal_separator'                       => get_option('decimal_separator'),
        'app_thousand_separator'                      => get_option('thousand_separator'),
        'app_currency_placement'                      => get_option('currency_placement'),
        'app_timezone'                                => get_option('default_timezone'),
        'app_calendar_first_day'                      => get_option('calendar_first_day'),
        'app_allowed_files'                           => get_option('allowed_files'),
        'app_show_table_export_button'                => get_option('show_table_export_button'),
        'app_desktop_notifications'                   => get_option('desktop_notifications'),
        'app_dismiss_desktop_not_after'               => get_option('auto_dismiss_desktop_notifications_after'),
    ];

    $lang = [
        'invoice_task_billable_timers_found'                      => _l('invoice_task_billable_timers_found'),
        'validation_extension_not_allowed'                        => _l('validation_extension_not_allowed'),
        'tag'                                                     => _l('tag'),
        'options'                                                 => _l('options'),
        'no_items_warning'                                        => _l('no_items_warning'),
        'item_forgotten_in_preview'                               => _l('item_forgotten_in_preview'),
        'email_exists'                                            => _l('email_exists'),
        'new_notification'                                        => _l('new_notification'),
        'estimate_number_exists'                                  => _l('estimate_number_exists'),
        'invoice_number_exists'                                   => _l('invoice_number_exists'),
        'confirm_action_prompt'                                   => _l('confirm_action_prompt'),
        'calendar_expand'                                         => _l('calendar_expand'),
        'proposal_save'                                           => _l('proposal_save'),
        'contract_save'                                           => _l('contract_save'),
        'media_files'                                             => _l('media_files'),
        'credit_note_number_exists'                               => _l('credit_note_number_exists'),
        'item_field_not_formatted'                                => _l('numbers_not_formatted_while_editing'),
        'filter_by'                                               => _l('filter_by'),
        'you_can_not_upload_any_more_files'                       => _l('you_can_not_upload_any_more_files'),
        'cancel_upload'                                           => _l('cancel_upload'),
        'remove_file'                                             => _l('remove_file'),
        'browser_not_support_drag_and_drop'                       => _l('browser_not_support_drag_and_drop'),
        'drop_files_here_to_upload'                               => _l('drop_files_here_to_upload'),
        'file_exceeds_max_filesize'                               => _l('file_exceeds_max_filesize') . ' (' . bytesToSize('', file_upload_max_size()) . ')',
        'file_exceeds_maxfile_size_in_form'                       => _l('file_exceeds_maxfile_size_in_form') . ' (' . bytesToSize('', file_upload_max_size()) . ')',
        'unit'                                                    => _l('unit'),
        'dt_length_menu_all'                                      => _l('dt_length_menu_all'),
        'dt_button_reload'                                        => _l('dt_button_reload'),
        'dt_button_excel'                                         => _l('dt_button_excel'),
        'dt_button_csv'                                           => _l('dt_button_csv'),
        'dt_button_pdf'                                           => _l('dt_button_pdf'),
        'dt_button_print'                                         => _l('dt_button_print'),
        'dt_button_export'                                        => _l('dt_button_export'),
        'search_ajax_empty'                                       => _l('search_ajax_empty'),
        'search_ajax_initialized'                                 => _l('search_ajax_initialized'),
        'search_ajax_searching'                                   => _l('search_ajax_searching'),
        'not_results_found'                                       => _l('not_results_found'),
        'search_ajax_placeholder'                                 => _l('search_ajax_placeholder'),
        'currently_selected'                                      => _l('currently_selected'),
        'task_stop_timer'                                         => _l('task_stop_timer'),
        'dt_button_column_visibility'                             => _l('dt_button_column_visibility'),
        'note'                                                    => _l('note'),
        'search_tasks'                                            => _l('search_tasks'),
        'confirm'                                                 => _l('confirm'),
        'showing_billable_tasks_from_project'                     => _l('showing_billable_tasks_from_project'),
        'invoice_task_item_project_tasks_not_included'            => _l('invoice_task_item_project_tasks_not_included'),
        'credit_amount_bigger_then_invoice_balance'               => _l('credit_amount_bigger_then_invoice_balance'),
        'credit_amount_bigger_then_credit_note_remaining_credits' => _l('credit_amount_bigger_then_credit_note_remaining_credits'),
        'save'                                                    => _l('save'),
    ];

    $js_vars = do_action('before_render_app_js_vars_admin', $js_vars);
    $lang    = do_action('before_render_app_js_lang_admin', $lang);

    echo '<script>';

    $firstKey = key($js_vars);

    $vars = 'var ' . $firstKey . '="' . $js_vars[$firstKey] . '",';

    unset($js_vars[$firstKey]);

    foreach ($js_vars as $var => $val) {
        $vars .= $var . '="' . $val . '",';
    }

    echo rtrim($vars, ',') . ';';

    echo 'var appLang = {};';
    foreach ($lang as $key => $val) {
        echo 'appLang["' . $key . '"] = "' . $val . '";';
    }

    echo '</script>';
}
/**
 * For html5 form accepted attributes
 * This function is used for the form attachments
 * @return string
 */
function get_form_accepted_mimes()
{
    $allowed_extensions  = get_option('allowed_files');
    $_allowed_extensions = explode(',', $allowed_extensions);
    $all_form_ext        = '';
    $CI                  = &get_instance();
    // Chrome doing conflict when the regular extensions are appended to the accept attribute which cause top popup
    // to select file to stop opening
    if ($CI->agent->browser() != 'Chrome') {
        $all_form_ext .= $allowed_extensions;
    }
    if (is_array($_allowed_extensions)) {
        if ($all_form_ext != '') {
            $all_form_ext .= ', ';
        }
        foreach ($_allowed_extensions as $ext) {
            $all_form_ext .= get_mime_by_extension($ext) . ', ';
        }
    }

    $all_form_ext = rtrim($all_form_ext, ', ');

    return $all_form_ext;
}

/**
 * CLear the session for the setup menu to be open
 * @return null
 */
function close_setup_menu()
{
    get_instance()->session->set_userdata([
        'setup-menu-open' => '',
    ]);
}

/**
 * Add http to url
 * @param  string $url url to add http
 * @return string
 */
function maybe_add_http($url)
{
    if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
        $url = 'http://' . $url;
    }

    return $url;
}
/**
 * Return specific alert bootstrap class
 * @return string
 */
function get_alert_class()
{
    $CI          = &get_instance();
    $alert_class = '';
    if ($CI->session->flashdata('message-success')) {
        $alert_class = 'success';
    } elseif ($CI->session->flashdata('message-warning')) {
        $alert_class = 'warning';
    } elseif ($CI->session->flashdata('message-info')) {
        $alert_class = 'info';
    } elseif ($CI->session->flashdata('message-danger')) {
        $alert_class = 'danger';
    }

    return $alert_class;
}

/**
 * Generate random alpha numeric string
 * @param  integer $length the length of the string
 * @return string
 */
function generate_two_factor_auth_key()
{
    $key  = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < 16; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    $key .= uniqid();

    return $key;
}
/**
 * Function that will replace the dropbox link size for the images
 * This function is used to preview dropbox image attachments
 * @param  string $url
 * @param  string $bounding_box
 * @return string
 */
function optimize_dropbox_thumbnail($url, $bounding_box = '800')
{
    $url = str_replace('bounding_box=75', 'bounding_box=' . $bounding_box, $url);

    return $url;
}
/**
 * Prepare label when splitting weeks for charts
 * @param  array $weeks week
 * @param  mixed $week  week day - number
 * @return string
 */
function split_weeks_chart_label($weeks, $week)
{
    $week_start = $weeks[$week][0];
    end($weeks[$week]);
    $key      = key($weeks[$week]);
    $week_end = $weeks[$week][$key];

    $week_start_year = date('Y', strtotime($week_start));
    $week_end_year   = date('Y', strtotime($week_end));

    $week_start_month = date('m', strtotime($week_start));
    $week_end_month   = date('m', strtotime($week_end));

    $label = '';

    $label .= date('d', strtotime($week_start));

    if ($week_start_month != $week_end_month && $week_start_year == $week_end_year) {
        $label .= ' ' . _l(date('F', mktime(0, 0, 0, $week_start_month, 1)));
    }

    if ($week_start_year != $week_end_year) {
        $label .= ' ' . _l(date('F', mktime(0, 0, 0, date('m', strtotime($week_start)), 1))) . ' ' . date('Y', strtotime($week_start));
    }

    $label .= ' - ';
    $label .= date('d', strtotime($week_end));
    if ($week_start_year != $week_end_year) {
        $label .= ' ' . _l(date('F', mktime(0, 0, 0, date('m', strtotime($week_end)), 1))) . ' ' . date('Y', strtotime($week_end));
    }

    if ($week_start_year == $week_end_year) {
        $label .= ' ' . _l(date('F', mktime(0, 0, 0, date('m', strtotime($week_end)), 1)));
        $label .= ' ' . date('Y', strtotime($week_start));
    }

    return $label;
}
/**
 * Get ranges weeks between 2 dates
 * @param  object $start_time date object
 * @param  objetc $end_time   date object
 * @return array
 */
function get_weekdays_between_dates($start_time, $end_time)
{
    $interval   = new DateInterval('P1D');
    $end_time   = $end_time->modify('+1 day');
    $dateRange  = new DatePeriod($start_time, $interval, $end_time);
    $weekNumber = 1;
    $weeks      = [];

    foreach ($dateRange as $date) {
        $weeks[$weekNumber][] = $date->format('Y-m-d');
        if ($date->format('w') == 0) {
            $weekNumber++;
        }
    }

    return $weeks;
}

function is_knowledge_base_viewable()
{
    return (get_option('use_knowledge_base') == 1 && !is_client_logged_in() && get_option('knowledge_base_without_registration') == 1) || (get_option('use_knowledge_base') == 1 && is_client_logged_in()) || is_staff_logged_in();
}


function _prepare_attachments_array_for_export($attachments)
{
    foreach ($attachments as $key => $item) {
        unset($attachments[$key]['id']);
        unset($attachments[$key]['visible_to_customer']);
        unset($attachments[$key]['staffid']);
        unset($attachments[$key]['contact_id']);
        unset($attachments[$key]['task_comment_id']);
    }

    return array_values($attachments);
}

function _prepare_items_array_for_export($items, $type)
{
    $cf = count($items) > 0 ? get_items_custom_fields_for_table_html($items[0]['rel_id'], $type) : [];

    foreach ($items as $key => $item) {
        $taxes     = [];
        $taxesFunc = 'get_' . $type . '_item_taxes';
        if (function_exists($taxesFunc)) {
            $taxes = call_user_func($taxesFunc, $item['id']);
            foreach ($taxes as $taxKey => $tax) {
                $t = explode('|', $tax['taxname']);

                $taxes[$taxKey]['taxname'] = $t[0];
                $taxes[$taxKey]['taxrate'] = $t[1];
            }
        }

        $items[$key]['tax']               = $taxes;
        $items[$key]['additional_fields'] = [];

        foreach ($cf as $custom_field) {
            $items[$key]['additional_fields'] = [
                 'name'  => $custom_field['name'],
                 'value' => get_custom_field_value($item['id'], $custom_field['id'], 'items'),
                ];
        }
    }

    return $items;
}
