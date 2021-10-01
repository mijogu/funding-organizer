<?php
/**
 * Extra functions for Funding Organizer theme
 */

 // Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/*
// TODO break apart functions.php into smaller files
// TODO decide on best way to break up functions

$roots_includes = array(
    '/functions/body-class.php',
    '/functions/connections.php'
  );

  foreach($roots_includes as $file){
    if(!$filepath = locate_template($file)) {
      trigger_error("Error locating `$file` for inclusion!", E_USER_ERROR);
    }

    require_once $filepath;
  }
  unset($file, $filepath);
  */


// Only show WP Admin bar to Admins
if (!current_user_can('administrator')) {
    show_admin_bar(false);
}

// constants
defined('FO_INVITE_COOKIE') or define('FO_INVITE_COOKIE', 'invite_id');
defined('FO_INVITE_PARAM') or define('FO_INVITE_PARAM', 'invite');


/**
 * Block non-administrators from accessing the WordPress back-end
 *
 * @return null
 */
function fo_block_users_backend()
{
    if (is_admin() && !current_user_can('administrator') && ! wp_doing_ajax()) {
        wp_redirect(site_url('/'));
        exit;
    }
}
add_action('init', 'fo_block_users_backend');


function understrap_remove_scripts()
{
    wp_dequeue_style('understrap-styles');
    wp_deregister_style('understrap-styles');

    wp_dequeue_script('understrap-scripts');
    wp_deregister_script('understrap-scripts');

    // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}
add_action('wp_enqueue_scripts', 'understrap_remove_scripts', 20);

add_action('wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles()
{

    // create my own version codes
    $my_js_ver  = date("ymd-Gis", filemtime( get_stylesheet_directory() . '/js/child-theme.min.js' ));
    // Using the below file instead of the child-theme.min.css because of a gulp issue.
    // The modified date is not updated correctly after compiling sass files.
    $my_css_min_ver = date("ymd-Gis", filemtime( get_stylesheet_directory() . '/css/child-theme.min.css.map' ));
    // $my_css_ver = date("ymd-Gis", filemtime( get_stylesheet_directory() . '/css/child-theme.css' ));

    // Get the theme data
    $the_theme = wp_get_theme();
    wp_enqueue_style('child-understrap-styles', get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $my_css_min_ver);
    wp_enqueue_script('jquery');
    //wp_enqueue_script('child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $my_css_min_ver, true);
    wp_enqueue_script('child-understrap-scripts', get_stylesheet_directory_uri() . '/js/child-theme.js', array(), $my_js_ver, true);

    // if (is_singular() && comments_open() && get_option('thread_comments')) {
    //     wp_enqueue_script('comment-reply');
    // }

    // enqueue Bootstrap Table js/css
    wp_enqueue_style('bootstrap-table-css', get_stylesheet_directory_uri(). '/css/bootstrap-table.min.css');
    wp_enqueue_script('bootstrap-table-js', get_stylesheet_directory_uri(). '/js/bootstrap-table.min.js', array('jquery'));

}

function add_child_theme_textdomain()
{
    load_child_theme_textdomain('understrap-child', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'add_child_theme_textdomain');



// REGISTER CUSTOM POST TYPE(S)
if (! function_exists('fo_create_post_types')) :

    function fo_create_post_types()
    {
        // You'll want to replace the values below with your own.
        register_post_type(
            'loanapplication',
            array(
                'labels' => array(
                    'name' => __('Loan Applications'),
                    'singular_name' => __('Loan Application'),
               ),
                'public' => true,
                'supports' => array ('title', 'custom-fields', 'author'),
                'hierarchical' => true,
                'menu_icon' => "dashicons-text-page",
                'rewrite' => array ('slug' => __('loanapplication')),
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
            )
        );

        // Create Submission Custom Post Type
        register_post_type( 'appsubmission', // change the name
            array(
                'labels' => array(
                    'name' => __( 'Submissions' ), // change the name
                    'singular_name' => __( 'Submission' ), // change the name
                ),
                'public' => true,
                'supports' => array ('title', 'custom-fields', 'author'), // do you need all of these options?
                'hierarchical' => false,
                'menu_icon' => "dashicons-saved",
                'rewrite' => false, // change the name
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
            )
        );
    }
    add_action('init', 'fo_create_post_types');

endif; // ####



if (class_exists('ACF')) {
    // note that the below code assumes there's an /acf-json/ directory
    // in the same directory the code is located.


    // Save ACF fields automatically
    add_filter(
        'acf/settings/save_json', function () {
            return dirname(__FILE__) . '/acf-json';
        }
    );

    // Load ACF fields automatically
    add_filter(
        'acf/settings/load_json', function ($paths) {
            $paths[] = dirname(__FILE__) . '/acf-json';
            return $paths;
        }
    );
}


/**
 * Redirect user to homepage after logout
 */
add_action('wp_logout', 'fo_redirect_after_logout');
function fo_redirect_after_logout()
{
    wp_redirect('/');
    exit();
}




/**
 * Displays the appropriate Advanced Form based on pageId
 *
 * @param int $pageId
 * @return void
 */
function fo_show_application_form($page_slug, $args = array())
{
    $userId = get_current_user_id();
    $loanApplicationId = get_field('loan_application_id', 'user_'.$userId);
    if (!$loanApplicationId) {
        $loanApplicationId = 'new';
    }

    $formPageData = fo_get_form_page_data_by_page_id($page_slug);
    $formId = $formPageData['formId'];
    $fieldGroupId = $formPageData['fieldGroupId'];

    if ($fieldGroupId != null) {
        fo_display_form_completion($loanApplicationId, $fieldGroupId);
    }

    $args['post'] = $loanApplicationId;
    $args['submit_text'] = "Save Application";

    if ($formId != null) {
        advanced_form($formId, $args);
    }
}


/**
 * Gets a specific form pages data based on page id
 *
 * @param $pageId can be int (page ID) or string (page slug)
 * @return array
 */
function fo_get_form_page_data_by_page_id($page_slug = 'all')
{
    $page_data = array(
        'company-information' => // Company information
            array(
                'fieldGroupId' => '128',
                'formId' => 'form_5d94d994d9e24',
                'includeInReview' => true
            ),
        'company-documents' => // Company documents
            array(
                'fieldGroupId' => '153',
                'formId' => 'form_5da77babe47fa',
                'includeInReview' => true
            ),
        'market-research' => // Market research
            array(
                'fieldGroupId' => '15',
                'formId' => 'form_5da7967378feb',
                'includeInReview' => true
            ),
        'securities-agreements' => // Securities agreement
            array(
                'fieldGroupId' => '245',
                'formId' => 'form_5dbc55d541714',
                'includeInReview' => true
            ),
        'debt-financing' => // Debt financing
            array(
                'fieldGroupId' => '262',
                'formId' => 'form_5dbc58a224eb8',
                'includeInReview' => true
            ),
        'material-agreements' => // Material agreements
            array(
                'fieldGroupId' => '273',
                'formId' => 'form_5dbc5ce4e17a2',
                'includeInReview' => true
            ),
        'litigation' => // Litigation
            array(
                'fieldGroupId' => '297',
                'formId' => 'form_5dbc5f3a94435',
                'includeInReview' => true
            ),
        'security-holder-information-reports' => // Security holder info
            array(
                'fieldGroupId' => '309',
                'formId' => 'form_5dbc60a247b15',
                'includeInReview' => true
            ),
        'business-operations' => // Business & operations
            array(
                'fieldGroupId' => '318',
                'formId' => 'form_5dbc62be440d3',
                'includeInReview' => true
            ),
        'employees' => // Employees
            array(
                'fieldGroupId' => '324',
                'formId' => 'form_5dbc6535d78d5',
                'includeInReview' => true
            ),
        'governmental-regulations-filings' => // Govt regulations
            array(
                'fieldGroupId' => '338',
                'formId' => 'form_5dbc66dfb8e78',
                'includeInReview' => true
            ),
        'intellectual-property' => // Intellectual property
            array(
                'fieldGroupId' => '346',
                'formId' => 'form_5dbc6952b547a',
                'includeInReview' => true
            ),
        'marketing-sales' => // Marketing & sales
            array(
                'fieldGroupId' => '357',
                'formId' => 'form_5dbc6b0fe3ed5',
                'includeInReview' => true
            ),
        'financial' => // Financial
            array(
                'fieldGroupId' => '367',
                'formId' => 'form_5dbc6c6a02a8e',
                'includeInReview' => true
            ),
        'references' => // References
            array(
                'fieldGroupId' => '385',
                'formId' => 'form_5dbc6e2cdc09c',
                'includeInReview' => true
            ),
        'funding-sources' => // Funding sources
            array(
                'fieldGroupId' => '390',
                'formId' => 'form_5dbc6ecf3d18a',
                'includeInReview' => true
            ),
        'assets' => // Assets
            array(
                'fieldGroupId' => '631',
                'formId' => 'form_5e8618ae7bc92',
                'includeInReview' => true
            ),
        'other-documents' => // Other documents
            array(
                'fieldGroupId' => '899',
                'formId' => 'form_5f6cbc11d2077',
                'includeInReview' => true
            ),
        'personal-financial-information' => // Personal Financial info
            array(
                'fieldGroupId' => '962',
                'formId' => 'form_6075b857e5426',
                'includeInReview' => true
            ),
        'corporate-real-estate-assets' => // Corporate Real Estate Assets
            array(
                'fieldGroupId' => '963',
                'formId' => 'form_6075b9939e221',
                'includeInReview' => true
            ),
        'machinery-equipment-appraisal-or-valuation' => // Machinery & Equipment Appraisal or Valuation
            array(
                'fieldGroupId' => '964',
                'formId' => 'form_6075b9c6b4f0a',
                'includeInReview' => true
            ),
        'affiliated-companies' => // Affiliated Companies
            array(
                'fieldGroupId' => '965',
                'formId' => 'form_6075ba883854c',
                'includeInReview' => true
            ),
        'working-capital-facilities' => // Working Capital Facilities
            array(
                'fieldGroupId' => '966',
                'formId' => 'form_6075baa5a9d5c',
                'includeInReview' => true
            ),
        'other-assets' => // Other Assets
            array(
                'fieldGroupId' => '967',
                'formId' => 'form_6075bb043d729',
                'includeInReview' => true
            ),
        'credit-release' => // Credit Release
            array(
                'fieldGroupId' => '1059',
                'formId' => 'form_6089e14987093',
                'includeInReview' => true
            ),
        '536' => // Submit Application
            array(
                'fieldGroupId' => null,
                'formId' => 'form_5e39b93ada6ac',
                'includeInReview' => false
            ),
    );

    if ($page_slug != 'all' && isset($page_data[$page_slug])) {
        return $page_data[$page_slug];
    } else {
        return $page_data;
    }
}


function fo_assign_new_loanapplication_to_user($post, $form, $args)
{
    $userId = get_current_user_id();

    if ($post->post_type == "loanapplication") {
         // link current User to Application that was just created
        update_field('loan_application_id', $post->ID, 'user_'.$userId);
    }
}
add_action('af/form/editing/post_created', 'fo_assign_new_loanapplication_to_user', 10, 3);


/**
 * Display the form completedness
 */
function fo_display_form_completion($loanApplicationId, $fieldGroupId)
{
    // TODO remove this when ready
    return;

    $fields = acf_get_fields($fieldGroupId);
    $totalFields = count($fields);
    $answeredFields = 0;
    if ($loanApplicationId != 'new') {
        foreach ($fields as $field) {
            if (get_field($field['name'], $loanApplicationId)) {
                $answeredFields++;
            }
        }
    }

    // how to calculate conditional fields?

    echo "<div class='alert alert-success'>You've answered $answeredFields out of $totalFields.</div>";
    // echo '<pre>'; var_dump($fields); echo '</pre>';
}


function fo_application_file_upload_dir( $path )
{
    // Determines if uploading from inside a post/page/cpt - if not, default Upload folder is used
    $the_post = null;
    $is_application = false;

    $post_id = ( !isset($_REQUEST['post_id'] ) || $_REQUEST['post_id'] == 0 ) ? null : $_REQUEST['post_id'];
    if ($post_id) {
        $the_post = get_post($_REQUEST['post_id']);
        $is_application = ( $the_post->post_type == 'loanapplication' ) ? true : false;
    }

    // don't change upload dir if error or NOT application
    if(!empty( $path['error'] ) || !$is_application) {
        return $path;
    }

    $customdir = fo_get_application_upload_path($the_post->ID);

    $path['path']    = str_replace($path['subdir'], '', $path['path']); //remove default subdir (year/month)
    $path['url']     = str_replace($path['subdir'], '', $path['url']);
    $path['subdir']  = $customdir;
    $path['path']   .= $customdir;
    $path['url']    .= $customdir;

    return $path;

}
add_filter( 'upload_dir', 'fo_application_file_upload_dir');


/**
 * Add bootstrap styling to Advanced Forms fields
 */
function fo_field_attributes( $attributes, $field, $form, $args ) {
    if (!empty($field['instructions'])) {
        $attributes['class'] .= ' has-instructions';
    }

    return $attributes;
}
add_filter( 'af/form/field_attributes', 'fo_field_attributes', 10, 4 );


/**
 * Add Funder role
 */
add_role('funder', __('Funder'), array());


// Display custom pagination
function fo_display_pagination($pages = '', $range = 4)
{
    $showitems = ($range * 2)+1;

    global $paged;
    // $btn_class = "btn btn-sm btn-outline-secondary";
    $btn_class = "page-link";
    if(empty($paged)) $paged = 1;

    if($pages == '')
    {
        global $wp_query;
        $pages = $wp_query->max_num_pages;
        if(!$pages)
        {
            $pages = 1;
        }
    }

    if(1 != $pages)
    {
        echo "<div class=\"pagination-links\">";
        echo "<div class=\"pagination pagination-sm\">";
        if($paged > 2 && $paged > $range+1 && $showitems < $pages) {
            echo "<li class=\"page-item\"><a href='".get_pagenum_link(1)."' class='".$btn_class."'>&laquo; First</a></li>";
        }
        if($paged > 1 && $showitems < $pages) {
            echo "<li class=\"page-item\"><a href='".get_pagenum_link($paged - 1)."' class='".$btn_class."'>&lsaquo; Previous</a></li>";
        }

        for ($i=1; $i <= $pages; $i++)
        {
            if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
            {
                if ($paged == $i) {
                    echo "<li class=\"page-item active\"><span class=\"$btn_class\">".$i."</span></li>";
                } else {
                    echo "<li class=\"page-item\"><a href='".get_pagenum_link($i)."' class=\"$btn_class\">".$i."</a></li>";
                }
            }
        }

        if ($paged < $pages && $showitems < $pages) {
            echo "<li class=\"page-item\"><a href=\"".get_pagenum_link($paged + 1)."\" class='".$btn_class."'>Next &rsaquo;</a></li>";
        }
        if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) {
            echo "<li class=\"page-item\"><a href='".get_pagenum_link($pages)."' class='".$btn_class."'>Last &raquo;</a></li>";
        }
        echo "</div>";
        echo "<span class=\"text-muted small mr-3\">Page ".$paged." of ".$pages."</span>";
        echo "</div>";
    }
}

// Get Application Table of Contents / TOC HTML
function fo_get_application_toc($application_id) {
    $fieldgroups = fo_get_form_page_data_by_page_id('all');

    $toc = '<div class="">'
        .'<p class="h5">Jump to Section</p>'
        .'<ol>';

    foreach ($fieldgroups as $fg) {
        if ($fg['fieldGroupId']) {
            $fieldGroup = acf_get_field_group($fg['fieldGroupId']);
            $toc .= '<li><a href="#section-'.$fieldGroup['ID'].'">'.$fieldGroup['title'].'</a></li>';
        }
    }

    $toc .= '</ol></div>';
    return $toc;
}

/**
 * Display loan application (frontend)
 */
function fo_get_application_html($application_id, $href_path = null)
{
    global $current_user;

    $is_admin = in_array('administrator', $current_user->roles);


    // Display user contact info
    $applicant_id = get_field('applicant_id', $application_id);
    $applicant = get_user_by('id', $applicant_id);
    $applicant_meta = get_user_meta($applicant_id);

    // get all Application Page data
    $fieldgroups = fo_get_form_page_data_by_page_id('all');

    // start output buffer
    ob_start();

    ?>
    <style>
    .note {
        border: 1px solid black;
        padding: 2px 10px;
        background-color: #fff3cd;
        display: inline-block;
    }
    </style>
    <?php

    // include download instructions
    if ($href_path !== null) {
        echo '<div class="note">';
        echo '<p>NOTE: Open this file in a browser for the document links below to work correctly.</p>';
        echo '</div>';
    }

    // echo '<base href="/" target="_blank">';
    echo '<div class="display-fieldgroup my-4 pb-4">';
    echo '<h2 class="field-group-title mb-4">Applicant Information</h2>';
    echo '<p><label class="field-label">Name:</label> <span class="field-value">'.$applicant_meta['first_name'][0].' ' .$applicant_meta['last_name'][0].'</span></p>';
    echo '<p><label class="field-label">Email:</label> <span class="field-value">'.$applicant->user_email.'</span></p>';

    // TODO submissions refactor

    echo '</div>';

    foreach ($fieldgroups as $fg) {
        if ($fg['includeInReview'] && $fg['fieldGroupId']) {
            $fields = array();
            $fieldGroup = acf_get_field_group($fg['fieldGroupId']);
            $fields = acf_get_fields($fg['fieldGroupId']);

            echo '<div id="section-'.$fieldGroup['ID'].'" class="display-fieldgroup fieldgroup-'.$fieldGroup['ID'].' my-4 pb-4">';
            echo '<h2 class="field-group-title mb-4">'.$fieldGroup['title'].'</h2>';

            if ($fields) {
                echo '<ol>';
                foreach ($fields as $f) {
                    $answered_class = get_field($f['name'], $application_id) ? 'answered' : 'not-answered';
                    echo '<li class="'.$answered_class.'">';
                    echo fo_display_application_field($f, $application_id, $href_path);
                    echo "</li>";
                }
                echo '</ol>';
            }
            echo '</div>';
        }
    }

    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function fo_display_application_field($field, $application_id, $href_path = null)
{
    $f = $field;
    $display_value = "";
    $output = "";

    if (!get_field($f['name'], $application_id)) {
        $display_value = "(not yet answered)";
    } elseif ($f['type'] == 'repeater') {
        //fo_render_repeater_field($value);
        $display_value = fo_display_repeater_field($f, $application_id, $href_path);
    } elseif ($f['type'] == 'file') {
        $display_value = fo_get_file_upload_field_display($f, $application_id, null, $href_path);
    } elseif ($f['type'] == 'url') {
        $display_value = fo_get_url_field_display($f, $application_id);
    // } elseif ($f['type'] == 'textarea') {
    //     $display_value = fo_get_text_field_display($f);
    } else {
        $display_value = fo_get_text_field_display($f, $application_id);
        //echo '<dt class="field-value">'.$display_value.'</dt>';
    }

    $output .= '<dl>';
    $output .= '<dt class="field-label">'.$field['label'].'</dt>';
    $output .= '<dt class="field-value">'.$display_value.'</dt>';
    $output .= '</dl>';

    return $output;
}

function fo_get_text_field_display($field, $application_id)
{
    // return '<b>TEXT FIELD</b>';
    $value = get_field($field['name'], $application_id);
    return $value;
}

function fo_get_url_field_display($field, $application_id)
{
    $value = get_field($field['name'], $application_id);
    return "<a href='$value' target='_blank'>$value</a>";
}

function fo_display_repeater_field($field, $application_id, $href_path = null)
{
    $values = get_field($field['name'], $application_id);
    $output = null;

    foreach ($values as $key=>$value) {
        // TODO loop thru and show values
        // another foreach here?
        foreach ($value as $k=>$v) {

            // if it's a file upload
            if (is_array($v) && array_key_exists( 'filename', $v)) {
                $v = fo_get_file_upload_field_display(null, $application_id, $v['ID'], $href_path);
            }

            $output .= '<p>';
            // $output .= '<span class="sub-field-label">'.$key.'</span>';
            $output .= '<span class="sub-field-value">'.$v.'</span>';
            $output .= '</p>';
        }
    }

    // return '<b>REPEATER FIELD</b>';
    return $output;
}

function fo_get_file_upload_field_display($field, $application_id, $attachmentId = null, $href_path = null)
{
    // This was copied from ACF Pro code
    // wp-content/plugins/advanced-custom-fields-pro/includes/fields/class-acf-field-file.php
    // $attachmentId = $attachementId;

    if ($attachmentId == null) {

        $field = get_field($field['name'], $application_id);
        $attachmentId = $field['ID'];
    }

    $attachment = acf_get_attachment($attachmentId);
    if( !$attachment ) return;

    // has value
    //$div['class'] .= ' has-value';

    // update
    $o['icon'] = $attachment['icon'];
    $o['title']	= $attachment['title'];
    // $o['url'] = $attachment['url'];
    $o['filename'] = $attachment['filename'];
    if ($href_path !== null) {
        $o['url'] = "./" . $href_path . $o['filename'];
    } else {
        $o['url'] = esc_url(site_url("dl-file.php?file=$attachmentId"));
    }

    if( $attachment['filesize'] ) {
        $o['filesize'] = size_format($attachment['filesize']);
    }

    // return '<a target="_blank" data-name="filename" href="' . esc_url($o['url']) . '">' . esc_html($o['filename']) .'</a>';
    return '<a target="_blank" href="' . $o['url'] . '">' . esc_html($o['filename']) .'</a>';
}


/**
 * Change the main menu
 */
// add_filter( 'wp_nav_menu_items', 'fo_change_main_nav', 10, 2 );
// function fo_change_main_nav( $items, $args ) {
add_filter( 'wp_nav_menu_objects', 'fo_change_main_nav', 10, 2 );
function fo_change_main_nav( $sorted_menu_objects, $args ) {
    global $current_user;
    if ($args->menu_id == 'main-menu') {
        //return $sorted_menu_objects;

        $remove_pages = array();

        if (is_user_logged_in()) {
            // remove login 92
            // remove register 94
            // remove about 401
            $remove_pages = array(92, 94, 401);

            // if not admin or funder, hide review applications
            if (!in_array('administrator', $current_user->roles) && !in_array('funder', $current_user->roles)) {
                $remove_pages[] = 408;
            } elseif (in_array('funder', $current_user->roles)) {
                $remove_pages[] = 106;
            }

            // get application id
            //$application_id = get_field('loan_application_id', "user_$current_user->ID");

            // if user already has an application, hide create application, else hide my application
            // $remove_pages[] = $application_id != '' ? 502 : 106;
        } else {
            // remove my application 106
            // remove edit profile 93
            // remove review applications 408
            // remove logout 95
            $remove_pages = array(95, 408, 93, 106);
        }
        foreach ($sorted_menu_objects as $key=>$item) {
            // edit logout url
            // if ($item->ID == 95) $item->url = wp_logout_url();
            if (in_array($item->ID, $remove_pages)) unset($sorted_menu_objects[$key]);
        }
    }
    elseif ($args->menu->term_id == 8 && in_array('subscriber', $current_user->roles)) {
        $application_id = get_field('loan_application_id', "user_$current_user->ID");
        foreach ($sorted_menu_objects as $key=>$item) {
            if ($application_id) {
                // change URL of Review Application for applicants
                // or remove it from the menu
                if ($item->ID == 832) {
                    $item->url = get_permalink($application_id);
                }
            } else {
                if ($item->ID == 832) {
                    unset($sorted_menu_objects[$key]);
                }
            }
        }
    } elseif ($args->menu_id == 'footer-menu') {

    }

    return $sorted_menu_objects;
}


/**
 * Process "Create Application" form
 * Assigns application ID to the current user
 */
function fo_create_application( $post, $form, $args ) {
    global $current_user;

    // get loanapplication ID
    $application_id = $post->ID;

    // save new fields to user profile

    // first_name
    update_user_meta($current_user->ID, 'first_name', $_POST["acf"]["field_5f6a5e24edbc9"]);
    // last_name
    update_user_meta($current_user->ID, 'last_name', $_POST["acf"]["field_5f6a5e2dedbca"]);
    // title
    update_field('title', $_POST["acf"]["field_5f6a630b9222b"], "user_$current_user->ID");
    // heard_about
    update_field('heard_about', $_POST["acf"]["field_5f6a62f39222a"], "user_$current_user->ID");


    // save Application ID to currentuser
    update_field('loan_application_id', $application_id, "user_$current_user->ID");
    // save User ID to Application
    update_field('applicant_id', $current_user->ID, $application_id);

    // if user is locked_to_funder
    $funder_id = get_field('locked_to_funder', "user_$current_user->ID");
    // if ($funder_id) {
    //     // add application_id to funder's inviter_started_applications list
    //     fo_add_remove_application_list($application_id, $funder_id, 'inviter_started_applications');
    // }

    // Redirect user to Edit Application page
    wp_redirect(site_url('/my-application'));
    exit;
}
add_action( 'af/form/editing/post_created/key=form_5e2f4a60a1fbb', 'fo_create_application', 10, 3 );

/**
 * Add Application sidebar
 */
function fo_custom_sidebars() {

    unregister_sidebar('right-sidebar');
    unregister_sidebar('left-sidebar');

    register_sidebar(
        array (
            'name' => __( 'Applicant Sidebar', 'understrap-child' ),
            'id' => 'applicant-sidebar',
            'description' => __( 'Applicant Sidebar', 'understrap-child' ),
            'before_widget' => '<div class="widget-content applicant-sidebar">',
            'after_widget' => "</div>",
            'before_title' => '<h5 class="widget-title mb-4">',
            'after_title' => '</h5>',
        )
    );
    register_sidebar(
        array (
            'name' => __( 'Funder Sidebar', 'understrap-child' ),
            'id' => 'funder-sidebar',
            'description' => __( 'Funder Sidebar', 'understrap-child' ),
            'before_widget' => '<div class="widget-content funder-sidebar">',
            'after_widget' => "</div>",
            'before_title' => '<h5 class="widget-title mb-4">',
            'after_title' => '</h5>',
        )
    );
    register_sidebar(
        array (
            'name' => __( 'Edit Application Sidebar', 'understrap-child' ),
            'id' => 'edit-application-sidebar',
            'description' => __( 'Edit Application Sidebar', 'understrap-child' ),
            'before_widget' => '<div class="widget-content applicant-sidebar">',
            'after_widget' => "</div>",
            'before_title' => '<h5 class="widget-title mb-4">',
            'after_title' => '</h5>',
        )
    );

}
add_action( 'widgets_init', 'fo_custom_sidebars' , 11);


function fo_get_sidebar_by_user_role($type = null)
{
    global $current_user;
    $application_id = get_field('loan_application_id', "user_$current_user->ID");

    if ($type && $type == 'application-toc') {
        echo '<div class="col-md-3 widget-area" id="left-sidebar" role="complementary">';
        echo fo_get_application_toc($application_id);
        echo '</div>';
    } elseif (in_array('funder', $current_user->roles)) {
        // check that funder is approved
        if (fo_is_funder_approved($current_user->ID) && is_active_sidebar( 'funder-sidebar' )) {
            echo '<div class="col-md-3 widget-area" id="left-sidebar" role="complementary">';
            dynamic_sidebar( 'funder-sidebar' );
            echo '</div>';
        }
    } elseif (in_array('administrator', $current_user->roles)) {
        echo '<div class="col-md-3 widget-area" id="left-sidebar" role="complementary">';
        dynamic_sidebar( 'funder-sidebar' );
        echo '</div>';
    } elseif (in_array('subscriber', $current_user->roles)) {
        // if (is_active_sidebar( 'applicant-sidebar' ) && has_active_application()) {
        //     echo '<div class="col-md-4 widget-area" id="left-sidebar" role="complementary">';
		// 	dynamic_sidebar( 'applicant-sidebar' );
        //     echo '</div>';
        // }
    }
}

function fo_render_page_header()
{
    global $current_user;

    if (in_array('subscriber', $current_user->roles)) { ?>
        <div class="row mb-5">
            <div class="col">
            <a href="<?php echo get_permalink(103); ?>" class="btn btn-outline-primary btn-sm">&larr; Back to Application Overview</a>
            </div>
        </div>
    <?php } else { ?>
            <div class="row mb-5">
                <div class="col">
                <a href="<?php echo get_permalink(406); ?>" class="btn btn-outline-primary btn-sm">&larr; Back to Manage Applications</a>
                </div>
            </div>
    <?php }
}

// Determine if current user has an active application
function fo_get_active_application($user_id = null)
{
    if (!$user_id) {
        global $current_user;
        $user_id = $current_user->ID;
    }
    $application_id = get_field('loan_application_id', "user_$user_id");
    return ($application_id != null && $application_id != "") ? $application_id : false;
}

function fo_can_submit_application($application_id = null)
{
    $can_submit = false;

    if ($application_id) {
        $credit_release = get_field('credit_release_form', $application_id);
        $can_submit = $credit_release ? true : false;
    }

    return $can_submit;
}


// Submit Application form - render Funders options
//add_filter('acf/load_field/name=choose_funders', 'fo_render_available_funder_choices', 3, 1);
// add_action('acf/render_field/name=choose_funders', 'fo_render_available_funder_choices', 1, 1);
add_filter('acf/prepare_field/name=choose_funders', 'fo_render_available_funder_choices', 3, 1);
function fo_render_available_funder_choices($field)
{
    ?>
    <input type="hidden" name="<?php echo $field['name'] ?>">
    <?php fo_render_funder_table($field['choices'], $field['id'], $field['name']); ?>
    <?php return false; // return false so field doesn't render itself // return $field;
}


// Display table of Funders with data
// Excecpt $funders to be formatted as $id => $name
function fo_render_funder_table($funders = null, $field_id = null, $field_name = null) {
    // no funders supplied, so return all approved funders
    if (!$funders) {
        $funders = fo_get_available_funders();
    }
    ?>
    <input id="custom-search" class="form-control search-input my-3" placeholder="Start typing to filter our Funding Partners..">
    <table
        class="table table-striped table-bordered align-middle funder-table"
        data-toggle="table"
        data-search="true"
        data-search-selector="#custom-search"

        <?php //data-search-highlight="true" ?>
    >
        <thead class="">
            <tr>
            <?php if ($field_id && $field_name) { ?>
                <th scope="col">&nbsp;</th>
            <?php } ?>
                <th width="20%" scope="col" data-sortable="true" data-field="name">Name</th>
                <th width="10%" scope="col" data-sortable="true" data-field="type">Type of Funder</th>
                <th width="10%" scope="col" data-sortable="true" data-field="sba">SBA Lender</th>
                <th width="20%" scope="col" data-field="loantypes">Types of Loans</th>
                <th width="15%" scope="col" data-field="loanamount">Loan Amount</th>
                <th width="10%" scope="col" data-field="geography">Geographic Coverage</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($funders as $funder_id => $funder_name) { ?>
        <?php
        $type_of_funding = get_field('type_of_funding', "user_$funder_id");
        $sba_lender = get_field('sba_lender', "user_$funder_id") ? 'Yes' : '';
        $types_of_loans = get_field('types_of_loans', "user_$funder_id");
        $types_of_loans = is_array($types_of_loans) ? implode(', ', $types_of_loans) : '';

        $min_loan = get_field('minumum_loan_size', "user_$funder_id", false);
        $max_loan = get_field('maximum_loan_size', "user_$funder_id", false);
        if ($min_loan) { $min_loan = '<sup>$</sup>' . number_format($min_loan, 0); }
        if ($max_loan) { $max_loan = '<sup>$</sup>' . number_format($max_loan, 0); }
        $loan_amount = '';
        if ($min_loan != '' && $max_loan != '') {
            $loan_amount = "$min_loan - $max_loan";
        } elseif ($min_loan) {
            $loan_amount = "Minimum $min_loan";
        } elseif ($max_loan) {
            $loan_amount = "Maximum $max_loan";
        }

        $geographic_coverage_area = get_field('geographic_coverage_area', "user_$funder_id");
        $geographic_coverage_area = is_array($geographic_coverage_area) ? implode(', ', $geographic_coverage_area) : '';
        ?>
        <tr>
        <?php if ($field_id && $field_name) { ?>
            <td class="align-middle"><input type="checkbox" id="<?php echo $field_id . '-' . $funder_id; ?>" name="<?php echo $field_name . '[]'; ?>" value="<?php echo $funder_id; ?>"></td>
        <?php } ?>
            <td class="align-middle"><?php echo $funder_name; ?></td>
            <td class="align-middle"><?php echo $type_of_funding; ?></td>
            <td class="align-middle"><?php echo $sba_lender; ?></td>
            <td class="align-middle"><?php echo $types_of_loans; ?></td>
            <td class="align-middle"><?php echo $loan_amount; ?></td>
            <td class="align-middle"><?php echo $geographic_coverage_area; ?></td>
        </tr>
    <?php } ?>
        </tbody>
    </table>
    <?php
}


// Submit Application form - load Funders as values
add_filter('acf/load_field/name=choose_funders', 'fo_load_available_funder_choices', 3, 1);
function fo_load_available_funder_choices($field) {
    global $current_user;
    $user_id = $current_user->ID;

    $application_id = get_field('loan_application_id', "user_$user_id");
    $locked_to_funder = get_field('locked_to_funder', "user_$user_id");
    $unlocked_application = get_field('unlocked_application', "user_$user_id");

    // reset choices
    $choices = array();
    $default_value = array();

    // if applicant is locked to funder
    if (intval($locked_to_funder) > 0 && !$unlocked_application) {
        $choices[$locked_to_funder] = get_field('funder_company_name', "user_$locked_to_funder");
        $default_value[$locked_to_funder] = get_field('funder_company_name', "user_$locked_to_funder");
        $default_value = $locked_to_funder;
    }
    // if applicant is UNLOCKED
    else {
        $choices = fo_get_available_funders($application_id);

        // loop through array and add to field 'choices'
        // foreach($available_funders as $funder) {
        //     $business_name = get_field('funder_company_name', "user_$funder->ID");
        //     $choices[$funder->ID] = $business_name ? $business_name : '(business name missing)';
        // }
    }
    // assign to field
    $field['choices'] = $choices;
    $field['default_value'] = $default_value;

    // return the field
    return $field;
}


// Returns a list of available funders for the given application.
// These are funders that have not been submitted to previously.
function fo_get_available_funders($application_id = null, $count_only = false)
{
    $funders_submitted_to = '';
    if ($application_id) {

        // TODO TEST
        $funders_submitted_to = fo_get_submissions(array('application_id' => $application_id), 'funder_id');
    }

    $args = array(
        'exclude' => $funders_submitted_to,
        'role' => 'funder',
        'meta_key' => 'funder_company_name',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'partner_funder_status',
                'value' => 'approved',
                'compare' => '='
            ),
            array(
                'key' => 'funder_status',
                'value' => 'approved',
                'compare' => '='
            )
        )
    );
    $available_funders = get_users($args);

    if ($count_only) {
        return count($available_funders);
    }

    $funder_list = array();
    foreach($available_funders as $funder) {
        $business_name = get_field('funder_company_name', "user_$funder->ID");
        $funder_list[$funder->ID] = $business_name ? $business_name : '(business name missing)';
    }

    return $funder_list;
}

// Generate an HTML list of Funders already submitted to
function fo_funders_submitted_to_list($application_id)
{
    // TODO TEST
    $current_funders = fo_get_submissions(array('application_id' => $application_id), 'funder_id');

    if(empty($current_funders)) return null;

    $funder_display_list = '<ul>';
    // display in a list
    foreach ($current_funders as $funder_id) {
        $business_name = get_field('funder_company_name', "user_$funder_id");
        $funder_display_list .= "<li>";
        $funder_display_list .= $business_name ? $business_name : '(business name missing)';
        $funder_display_list .= "</li>";
    }
    $funder_display_list .= '</ul>';
    return $funder_display_list;
}

function fo_add_hidden_field_to_application_submission( $form, $args )
{
    global $current_user;
    $user_id = $current_user->ID;
    $funder_type = 'partner';
    $locked_to_funder = get_field('locked_to_funder', "user_$user_id");
    $unlocked_application = get_field('unlocked_application', "user_$user_id");
    if ($locked_to_funder != '' && !$unlocked_application) {
        $funder_type = 'inviter';
    }

    echo '<input type="hidden" name="funder_type" value="'.$funder_type.'">';
}
add_action( 'af/form/hidden_fields/key=form_5e39b93ada6ac', 'fo_add_hidden_field_to_application_submission', 10, 2 );


// Process Submit application form
function fo_submit_application( $post, $form, $args )
{
    global $current_user;
    $user_id = $current_user->ID;
    $application_id = get_field('loan_application_id', "user_$user_id");

    // get list of Funders to submit to
    $new_funder_submissions = af_get_field('choose_funders');
    // $funder_type = af_get_field('funder_type');
    $funder_type = isset($_POST['funder_type']) ? $_POST['funder_type'] : 'partner';

    // TODO test the new_funder_submissions array?
    fo_process_new_submissions($application_id, $new_funder_submissions, $funder_type);

}
add_action( 'af/form/submission/key=form_5e39b93ada6ac', 'fo_submit_application', 10, 3 );


// Process a list of new Funder IDs to submit application to
function fo_process_new_submissions($application_id, $funder_ids, $type)
{
    if (!$application_id || empty($funder_ids) || !$type) {
        return;
    }

    $applicant_id = get_field('applicant_id', $application_id);
    $business_name = get_field('company_name', $application_id);
    $loan_amount = "$".get_field('loan_amount', $application_id);
    $site_url = site_url();

    // trigger email to funder
    $message = file_get_contents(get_stylesheet_directory(). '/email-templates/funder-new-application.html');
    $message = sprintf($message, $business_name, $loan_amount, $site_url);
    $subject = 'Funding Organizer: new Loan Application to Review';

    // loop thru new funders
    foreach ($funder_ids as $funder_id) {
        $funder = get_userdata($funder_id);
        $funder_email = $funder->user_email;

        // create new Submission post
        $post_id = wp_insert_post(
            array(
                'post_author'   => $applicant_id,
                'post_title'    => $business_name,
                'post_status'   => 'publish',
                'post_type'   => 'appsubmission'
            )
        );
        // update acf fields
        update_field('application_id', $application_id, $post_id);
        update_field('applicant_id', $applicant_id, $post_id);
        update_field('funder_id', $funder_id, $post_id);
        update_field('status', 'pending', $post_id);
        update_field('type', $type, $post_id);

        // send email
        fo_generate_email($funder_email, $subject, $message);
    }
}


/**
* Send registration received email
*/
function fo_generate_email($user_email, $subject, $message)
{
    add_filter( 'wp_mail_content_type', 'fo_set_html_mail_content_type' );

    // send email
    wp_mail($user_email, $subject, $message);

    // Reset content-type to avoid conflicts -- https://core.trac.wordpress.org/ticket/23578
    remove_filter( 'wp_mail_content_type', 'fo_set_html_mail_content_type' );
}

function fo_set_html_mail_content_type() {
    return 'text/html';
}

add_filter('wp_mail','fo_prevent_development_emails', 100, 1);
function fo_prevent_development_emails($args){
    // override email address for local development testing
    if (FO_LOCAL_ENV) {
        $orig_email = $args['to'];
        $args['to'] = "admin@darngood.io";
        $args['message'] .= "[FO_LOCAL_ENV] overriding the original email recipient: $orig_email";
        //$args['subject']
        //$args['message']
        //$args['headers']
        //$args['attachments']
    }

    return $args;
  }


/**
 * Hook into the ACF label field
 * Add button to trigger the "why" explanations
 */
add_filter('acf/load_field', 'fo_acf_add_why_button');

function fo_acf_add_why_button($field)
{
    $page_template = get_page_template_slug();

    // only want to show this on the frontend forms
    if ( $page_template != 'form-page.php') {
        return $field;
    }

    $button = " <a class='why-button' >Why?</a>";

    // if field has instructions
    if ($field['instructions'] != "") {
        // add button to label
        $field['label'] .= $button;
    }

    return $field;
}


// Change default button label (from Add Row to Upload File)
add_filter('acf/prepare_field/type=repeater', 'fo_change_add_row_button_label', 3);

function fo_change_add_row_button_label($field) {
    $page_template = get_page_template_slug();

    // only want to show this on the frontend forms
    if ( $page_template != 'form-page.php') {
        return $field;
    }

    if (isset($field['button_label']) && $field['button_label'] == "") {
        $field["button_label"] = "Upload file";
    } else {
        $label = "";
    }

    return $field;
}


// Determine if user's profile is complete
function fo_is_profile_complete()
{
    global $current_user;
    $user_id = $current_user->ID;
    $profile_complete = true;
    $fields_required = array('first_name', 'last_name');

    foreach ($fields_required as $field) {
        $val = get_user_meta($user_id, $field, true);
        if (empty($val)) $profile_complete = false;
    }

    return $profile_complete;
}


// Check if user has access to application
// Also used in dl_file.php
function fo_user_has_access_to_application($application_id)
{
    global $current_user;
    $user_id = $current_user->ID;
    $has_access = false;
    $application_ids = array();

    if (in_array('administrator', $current_user->roles)) {
        $has_access = 'admin';
    } elseif (in_array('funder', $current_user->roles)) {

        // TODO TEST
        $filters = array(
            'application_id' => $application_id,
            'funder_id' => $user_id
        );
        $application_ids = fo_get_submissions($filters, 'application_id');

        if (in_array($application_id, $application_ids)) {
            $has_access = 'funder';
        }
    } else {
        $user_application_id = get_field('loan_application_id', "user_$user_id");
        if ($user_application_id == $application_id) {
            $has_access = 'applicant';
        }
    }
    return $has_access;
}

// Get Submissions
function fo_get_submissions($filters = array(), $single_field = false, $per_page = null, $paged = 1)
{
    $per_page = is_int($per_page) ? $per_page : '-1';

    // build meta query
    $meta_queries = array();
    foreach($filters as $key => $value) {
        $meta_queries[] = array(
            'key' => $key,
            'value' => $value,
            'compare' => '='
        );
    }
    if (count($meta_queries) >= 2) {
        $meta_queries['relation'] = 'AND';
    }
    $args = array(
        'post_type' => 'appsubmission',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'meta_query' => $meta_queries
    );

    // get submissions
    $submissions = get_posts($args);

    // check if we only want the Application IDs
    if ($single_field) {
        $submissions = array_map(function($sub) use ($single_field) {
            return get_field($single_field, $sub->ID);
        }, $submissions);
    }

    return $submissions;
}

function fo_get_locked_applications_for_funder($funder_id = null, $type = 'unsubmitted')
{
    $applicants = array();
    $applications = array();

    if (!$funder_id) {
        return $applications;
    }

    $args = array(
        'role__in' => array('subscriber'),
        'meta_key' => 'locked_to_funder',
        'meta_value' => $funder_id
    );

    // get applicants
    $applicants = get_users($args);

    // get application IDs from applicants
    foreach ($applicants as $applicant) {
        $application_id = get_field('loan_application_id', "user_$applicant->ID");
        if ($application_id) {
            $applications[] = $application_id;
        }
    }

    // get submissions
    $submissions = fo_get_submissions(array('funder_id' => $funder_id), 'application_id');

    // get unique application IDs
    $unsubmitted_applications = array_diff($applications, $submissions);

    return $unsubmitted_applications;
}


//
// function fo_generate_reset_password_link($user)
// {
//     if (!$user) return false;

//     $user_login = rawurlencode($user->user_login);
//     $reset_password_key = get_password_reset_key($user);
//     $reset_password_link = site_url('resetpass') . "?key=$reset_password_key&login=$user_login&first=true";

//     return $reset_password_link;
// }


// add_action('edit_user_profile_update', 'fo_send_funder_approval_email', 10, 1);
add_action('profile_update', 'fo_send_funder_approval_email', 11, 2);
// Determines if we should trigger the Funder or Partner Funder approval emails
function fo_send_funder_approval_email($user_id, $old_user_data)
{
    $user = get_user_by('id', $user_id);

    // check Funder status
    // check Funder approval email sent
    $funder_status = get_field('funder_status', "user_$user_id");
    $funder_approval_email_sent = get_field('is_funder_approved_email_sent', "user_$user_id");

    // check Partner funder status
    // check Partner funder approval email sent
    $partner_status = get_field('partner_funder_status', "user_$user_id");
    $partner_approval_email_sent = get_field('is_partner_funder_approved_email_sent', "user_$user_id");

    if ($funder_status == 'approved' && !$funder_approval_email_sent) {
        // generate approval email

        $message = file_get_contents(get_stylesheet_directory(). '/email-templates/funder-approved.html');
        //$reset_password_link = fo_generate_reset_password_link($user);
        $message = sprintf($message);
        $subject = 'You are approved as a Funder at Funding Organizer!';
        fo_generate_email($user->user_email, $subject, $message);

        update_field('is_funder_approved_email_sent', true, "user_$user_id");

    } elseif ($partner_status == 'approved' && !$partner_approval_email_sent) {
        // generate approval email

        // TO DO is this a new user or not???
        $message = file_get_contents(get_stylesheet_directory(). '/email-templates/partner-funder-approved.html');
        //$reset_password_link = fo_generate_reset_password_link($user);
        $subject = 'You are approved as a Funder at Funding Organizer!';
        fo_generate_email($user->user_email, $subject, $message);

        update_field('is_partner_funder_approved_email_sent', true, "user_$user_id");
    }
}

function fo_get_funder_invite_link($user_id = null)
{
    global $current_user;
    if ($user_id == null) {
        $user_id = $current_user->ID;
    }
    $invite_id = get_field('unique_invite_code', "user_$user_id");

    return add_query_arg('invite', $invite_id, get_site_url(null, 'register'));
}


function fo_generate_funder_invite_id($user_id)
{
    global $wpdb;
    $unique = false;
    $id = null;
    $length = 13;

    while (!$unique) {
        // generate code
        // $id = uniqid(rand(), true);
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $id = substr(str_shuffle($permitted_chars), 0, $length);

        // make sure not a duplicate
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->usermeta meta WHERE meta_key = '$id'");

        if ($count == 0) $unique = true;
    }
    return $id;
}

// add_action('edit_user_profile_update', 'fo_check_for_funder_invite_id', 10, 1);
// add_action('user_register', 'fo_check_for_funder_invite_id', 11, 1);
add_action('profile_update', 'fo_check_for_funder_invite_id', 12, 2);

function fo_check_for_funder_invite_id($user_id, $old_user_data)
{
    $user = get_user_by('id', $user_id);
    // return if not a funder
    if (!in_array('funder', $user->roles)) {
        return;
    }
    // return if already has an invite id
    if (get_field('unique_invite_code', "user_$user_id") != "") { // unique_invite_code
        return;
    }
    // generate invite id
    $id = fo_generate_funder_invite_id($user_id);
    // save to funder user
    update_field('unique_invite_code', $id, "user_$user_id");
}


// Increase the default password reset expiration time
add_filter( 'password_reset_expiration', function( $expiration ) {
    return 2 * WEEK_IN_SECONDS;
});



// Central place to control the included fields for Advanced Forms.
// Using this to determine the $exclude_fields
function fo_get_excluded_fields_by_page($page, $fields_to_return = 'excluded')
{
    $exclude_fields = array();
    $include_fields = array();
    $field_group = null;

    switch ($page) {
        case 'funder-signup' :
        case 'funder-profile' :
            $field_group = '545';
            $include_fields = array(
                'field_5e39d2ab96c87', // funder co name
                'field_5ec6a2fe0e533', // first name
                'field_5ec6a3560e534', // last name
                'field_5ec6a4320e535', // title
                'field_5ec6a4820e536', // type of funding
                'field_5ec6a4f40e537', // address
                'field_5ec6a5160e538', // office phone
                'field_5ec6a5290e539', // cell phone
                'field_5ec6a5310e53a', // email
                'field_5efba039ca023', // found out about FO?
            );
        break;
        case 'partner-signup' :
        case 'partner-profile' :
            $field_group = '545';
            $include_fields = array(
                'field_5e39d2ab96c87', // funder co name
                'field_5ec6a2fe0e533', // first name
                'field_5ec6a3560e534', // last name
                'field_5ec6a4320e535', // title
                'field_5ec6a4820e536', // type of funding
                'field_5ec6a4f40e537', // address
                'field_5ec6a5160e538', // office phone
                'field_5ec6a5290e539', // cell phone
                'field_5ec6a5310e53a', // email
                'field_5ec6a7887f713', // sba lender
                'field_5ec6a7c97f714', // types of loans
                'field_5ec6a8297f715', // minimum loan
                'field_5ec6a8697f716', // maximum loan
                'field_5ec6a88c7f717', // us geographic coverage
                'field_60917ba62c17b', // non-us geographic coverage
                'field_5efba039ca023', // found out about FO?
            );
        break;
        default:
            $field_group = '60';
            $include_fields = array(
                'field_5ec956dd60a32', // email
                'field_5ec9584594cbb', // first name
                'field_5ec9584f94cbc', // last name
                'field_5ec958976212c', // title
                'field_5efba1ace70e5', // found out about FO?
            );
        break;
    }

    if ($fields_to_return != 'excluded') return $include_fields;

    // if no include fields supplied, don't exclude any.
    // show all field from field group
    if (!empty($include_fields)) {
        $all_fields = acf_get_fields($field_group);
        foreach($all_fields as $field) {
            // if not in $include_fields, add to $exclude_fields
            if (!in_array($field['key'], $include_fields)) {
                $exclude_fields[] = $field['key'];
            }
        }
    }
    return $exclude_fields;
}


function fo_render_loan_amount_field($value, $post_id, $field)
{
    echo number_format($value);
}
add_action('acf/format_value/key=field_5ec6a8697f716', 'fo_render_loan_amount_field', 10, 3);
add_action('acf/format_value/key=field_5ec6a8297f715', 'fo_render_loan_amount_field', 10, 3);


// Handle "invite" code when Funder invites Applicant
// If GET param isset, save as a cookie
// add_action('init', 'fo_set_invite_cookie');
add_action('init', 'fo_set_invite_cookie');
function fo_set_invite_cookie()
{
    // check for GET param
    if (isset($_GET['invite']) && !empty($_GET['invite'])) {
        $invite_id = $_GET['invite'];
        $cookie_set = setcookie(FO_INVITE_COOKIE, $invite_id, time() + (2 * WEEK_IN_SECONDS));
    }
}


add_action( 'init', 'fo_add_business_registration_fields', 11);
function fo_add_business_registration_fields()
{
    $invite_id = null;
    if (isset($_COOKIE[FO_INVITE_COOKIE])) {
        $invite_id = $_COOKIE[FO_INVITE_COOKIE];
    } elseif(isset($_GET[FO_INVITE_PARAM]) && !empty($_GET[FO_INVITE_PARAM])) {
        $invite_id = $_GET[FO_INVITE_PARAM];
    }
    if ($invite_id == null) {
        return;
    }
    tml_add_form_field( 'register', 'invite_id', array(
        'type'     => 'hidden',
        'type'     => 'text',
        'value'    => $invite_id
    ));
}


add_action( 'user_register', 'fo_set_business_user_role' );
function fo_set_business_user_role($user_id) {
	if (isset($_POST['invite_id'])) {
        $invite_id = $_POST['invite_id'];
        // get funder by invite id
        $args = array(
            'meta_key' => 'unique_invite_code',
            'meta_value' => $invite_id,
            'number' => 1,
            'count_total' => false
        );
        // $inviting_funder = reset(get_users($args));
        // $inviting_funder = fo_get_user_by_meta_data('unique_invite_code', $invite_id);
        $inviting_funder = fo_get_user_by_meta_data('unique_invite_code', $invite_id);

        // set invite ID
        update_field('invite_id', $invite_id, "user_$user_id");
        // set limited to funder
        update_field('locked_to_funder', $inviting_funder->ID, "user_$user_id");
        // remove cookie
        setcookie(FO_INVITE_COOKIE, time() - 3600);
    }
}


function fo_get_user_by_meta_data($meta_key, $meta_value)
{
	// Query for users based on the meta data
	$user_query = new WP_User_Query(
		array(
			'meta_key'	  =>	$meta_key,
			'meta_value'	=>	$meta_value
		)
	);
	// Get the results from the query, returning the first user
	$users = $user_query->get_results();
	return $users[0];
}


// Checks whether or not funder account is approved
function fo_is_funder_approved($user_id)
{
    $is_approved = get_field('funder_status', "user_$user_id") == 'approved' ? true : false;
    return $is_approved;
}

// Add bootstrap modal confirm attributes to AF submit buttons
function fo_submit_button_attributes($attributes, $form, $args ) {
    $attributes['data-toggle'] = 'modal';
    $attributes['data-target'] = '#confirm-submit';
    $attributes['id'] = 'attempt-submit-application';
    $attributes['class'] .= ' modal-confirm-submit';

    return $attributes;
}
// add_filter( 'af/form/button_attributes/key=form_5e39b93ada6ac', 'fo_submit_button_attributes', 10, 3 );

// Add bootstrap styling to AF submit buttons
function fo_submit_button_bootstrap_classes($attributes, $form, $args)
{
    $attributes['class'] .= ' btn btn-primary';
    return $attributes;
}
add_filter('af/form/button_attributes', 'fo_submit_button_bootstrap_classes', 10, 3);


function fo_handle_partner_funder_signup( $user, $form, $args )
{
    $user_id = $user->ID;
    update_field('partner_funder_status', 'pending' , "user_$user_id");
}
add_action( 'af/form/editing/user_created/key=form_5ec6b9b74209e', 'fo_handle_partner_funder_signup', 10, 3 );
// add_action( 'af/form/editing/user_updated/key=form_5ec6b9b74209e', 'fo_handle_partner_funder_signup', 10, 3 );

function fo_render_approve_reject_buttons($application_id)
{
    global $current_user;
    $user_id = $current_user->ID;

    // TODO TEST

    $filters = array(
        'application_id' => $application_id,
        'funder_id' => $user_id
    );
    $submission = fo_get_submissions($filters);
    // TODO check for duplicate submissions and remove
    $submission_id = $submission[0]->ID;
    $status = get_field('status', $submission_id);

    if (!$submission) return;

    if ($status == 'pending') {
        $nonce = wp_create_nonce("funder_approve_reject_nonce");
        // $approve_link = admin_url('admin-ajax.php?action=funder_approve_reject&access_type='.$type.'&action_type=approved&user_id='.$user_id.'&application_id='.$application_id.'&nonce='.$nonce);
        // $reject_link = admin_url('admin-ajax.php?action=funder_approve_reject&access_type='.$type.'&action_type=rejected&user_id='.$user_id.'&application_id='.$application_id.'&nonce='.$nonce);
        $approve_link = admin_url('admin-ajax.php?action=funder_approve_reject&submission='.$submission_id.'&action_type=approved&nonce='.$nonce);
        $reject_link = admin_url('admin-ajax.php?action=funder_approve_reject&submission='.$submission_id.'&action_type=rejected&nonce='.$nonce);

        // TODO add jquery support for these buttons (not needed at launch)
        // TODO determine which button should be shown - MVP only show if app id is in review
        ?>
        <div class="application-action-buttons">
            <?php /*
            <a class="btn btn-success" href="<?php echo $approve_link; ?>" data-nonce="<?php echo $nonce;?>" data-action_type="approved" data-user_id="<?php echo $user_id; ?>" data-application_id="<?php echo $application_id; ?>">Approve</a>
            <a class="btn btn-warning" href="<?php echo $reject_link; ?>" data-nonce="<?php echo $nonce;?>" data-action_type="rejected" data-user_id="<?php echo $user_id; ?>" data-application_id="<?php echo $application_id; ?>">Reject</a>
            */ ?>
            <a class="btn btn-success" href="<?php echo $approve_link; ?>" data-nonce="<?php echo $nonce;?>" data-action_type="approved" data-submission="<?php echo $submission_id; ?>">Approve</a>
            <a class="btn btn-warning" href="<?php echo $reject_link; ?>" data-nonce="<?php echo $nonce;?>" data-action_type="rejected" data-submission="<?php echo $submission_id; ?>">Reject</a>
        </div>
    <?php } elseif ($status == 'approved') { ?>
        <div class="alert alert-success">
            You approved this application.
        </div>
    <?php } elseif ($status == 'rejected') { ?>
        <div class="alert alert-warning">
            You rejected this application.
        </div>
    <?php }
}

// example = https://premium.wpmudev.org/blog/using-ajax-with-wordpress/
add_action("wp_ajax_funder_approve_reject", "fo_process_approve_reject_application");
add_action("wp_ajax_nopriv_funder_approve_reject", "fo_prompt_login");

// define the function to be fired for logged in users
function fo_process_approve_reject_application()
{
    // nonce check for an extra layer of security, the function will exit if it fails
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "funder_approve_reject_nonce")) {
        die();
    }

    // get values from request
    $action_type = $_REQUEST['action_type'];
    $submission_id = $_REQUEST['submission'];

    // get values from Submission
    $funder_id = get_field('funder_id', $submission_id);
    $application_id = get_field('application_id', $submission_id);

    // TODO TEST

    // update the submission
    $result = update_field('status', $action_type, $submission_id);

    // get applicant info
    $applicant_id = get_field('applicant_id', $application_id);
    $applicant_data = get_userdata($applicant_id);
    $business_name = get_field('company_name', $application_id);
    $funder_company = get_field('funder_company_name', "user_$funder_id");

    if ($action_type == 'approved') {
        // send approval email
        $message = file_get_contents(get_stylesheet_directory(). '/email-templates/application-approved.html');
        $message = sprintf($message, $business_name, $funder_company);
        $subject = 'Your Loan Application has been approved';
        fo_generate_email($applicant_data->user_email, $subject, $message);
    } elseif ($action_type == 'rejected') {
        // send rejection email
        $message = file_get_contents(get_stylesheet_directory(). '/email-templates/application-rejected.html');
        $message = sprintf($message, $business_name, $funder_company);
        $subject = 'Your Loan Application has been rejected';
        fo_generate_email($applicant_data->user_email, $subject, $message);
    }

    // TODO return success message

    // Check if action was fired via Ajax call. If yes, JS code will be triggered, else the user is redirected to the post page
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $result = json_encode($result);
        echo $result;
    }
    else {
        // approved, rejected, error
        header("Location: ".$_SERVER["HTTP_REFERER"]."?result=$action_type");
    }

    // don't forget to end your scripts with a die() function - very important
    die();
}

function fo_prompt_login()
{
    echo "You must be logged in to do this.";
    wp_die();
}



function fo_render_applicants_table($applications, $link_to = true)
{
    global $post;
    $i = 0;

    if ($applications->have_posts()) {
    ?>
    <table class="table">
        <thead class="thead-light">
            <th scope="col">#</th>
            <th scope="col">Applicant / Company</th>
            <th scope="col">Loan Amount / Usage</th>
        </thead>
        <tbody>
        <?php while ($applications->have_posts()) { ?>
            <?php $applications->the_post(); ?>

        <?php //foreach ($applications as $i=>$application) { ?>
            <?php
            $user_id = get_field('applicant_id', $post->ID);
            $user_data = get_userdata($user_id);
            $user_name = "$user_data->first_name $user_data->last_name";
            $user_email = $user_data->user_email;
            $loan_amount = (float)get_field('loan_amount', $post->ID);
            $uses_of_loan = get_field('uses_of_loan', $post->ID);
            ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td>
                    <?php if ($link_to) { ?><a href="<?php echo get_permalink($post); ?>"><?php } ?>
                        <strong><?php echo $post->post_title; ?></strong>
                    <?php if ($link_to) { ?></a><?php } ?>
                <br>
                <?php echo $user_name; ?><br>
                <?php echo $user_email; ?>
                </td>
                <td>
                    <?php echo "$". number_format($loan_amount); ?><br>
                    <?php echo $uses_of_loan; ?>
                </td>
            </tr>
            <?php $i++; ?>
        <?php } // endwhile ?>
        </tbody>
    </table>
    <?php } // endif

    wp_reset_postdata();

}


// add Edit User link to Funder signup emails
add_filter( 'af/form/email/content/key=form_5ec6a55b507c4', 'filter_email_content', 10, 4 ); // form_5ec6a55b507c4 = funder
add_filter( 'af/form/email/content/key=form_5ec6b9b74209e', 'filter_email_content', 10, 4 ); // form_5ec6b9b74209e = partner

function filter_email_content( $content, $email, $form, $fields ) {
    $user_email = null;

    foreach($fields as $field) {
        if ($field['name'] == 'email') {
            $user_email = $field['value'];
            break;
        }
    }

    $user = get_user_by('email', $user_email);
    // $link = get_edit_user_link($user->ID);
    $link = get_site_url()."/wp-admin/user-edit.php?user_id=$user->ID";
    $content .= "<p><a href='$link'>Approve user</a></p>";

    // TODO improve login redirect link
    // wp_login_url( string $redirect = '', bool $force_reauth = false )
    // For best results, use site_url( ‘/mypage/ ‘ ).
    return $content;
}

// Restrict REST API
// Recommened by WordPress
// https://developer.wordpress.org/rest-api/frequently-asked-questions/#can-i-disable-the-rest-api
add_filter( 'rest_authentication_errors', function( $result ) {
    // If a previous authentication check was applied,
    // pass that result along without modification.
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }

    // No authentication has been performed yet.
    // Return an error if user is not an admin.
    $current_user = wp_get_current_user();
    if (!user_can( $current_user, 'administrator' )) {
    // if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            __( 'You are not currently logged in.' ),
            array( 'status' => 401 )
        );
    }

    // Our custom authentication check should have no effect
    // on logged-in requests
    return $result;
});


// Remove hidden pages from Yoast SEO Sitemap.
// Loan Applications hidden from SEO > Search Appearance.
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function () {
    return array(
        895, 853, 804, 801, 769, 765, 660, 536, 521, 500,
        406, 207, 205, 202, 200, 198, 196, 194, 191, 189,
        187, 183, 115, 113, 111, 103, 77, 51, 49, 47,
        960, 958, 956, 954, 952, 950 // new application sections WP Pages - April 2021
    );
});


// Get the absolute path to the application
// directory. Used to store uploaded files.
function fo_get_application_upload_path($application_id) {
    return "/private/app$application_id";
}

// Bulk download of all Application Files
function fo_bulk_download_application_link($applicationId) {
    return site_url("dl-file.php?bulk=$applicationId");
}

// Stripped down print version of an Application
function fo_print_application_link($applicationId) {
    return site_url("dl-file.php?print=$applicationId");
}


// Add User Columns
function fo_new_modify_user_table( $columns ) {
    $columns['application'] = 'Application';
    $columns['funder-company-name'] = 'Funding Company';
    // $columns['funder-status'] = 'Funder status';
    return $columns;
}
add_filter( 'manage_users_columns', 'fo_new_modify_user_table' );

function fo_new_modify_user_table_row( $val, $column_name, $user_id ) {
    switch ($column_name) {
        case 'application' :
            $application_id = get_field('loan_application_id', "user_$user_id");
            if ($application_id) {
                $application = get_post($application_id);
                $title = "#". $application_id . " " . $application->post_title;
                $link = get_edit_post_link($application_id);
                return "<a href='$link'>$title</a>";
            }
        break;
        case 'funder-company-name':
            return get_field('funder_company_name', "user_$user_id");
        break;
        // case 'funder-status':
        //     return get_field('', "user_$user_id");
        // break;
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'fo_new_modify_user_table_row', 10, 3 );


// Add Submissions Columns
function fo_modify_submissions_table( $columns ) {
    $columns['application_id'] = 'Application ID';
    $columns['applicant_id'] = 'Applicant ID';
    $columns['funder_id'] = 'Funding ID';
    $columns['status'] = 'Status';
    return $columns;
}
add_filter( 'manage_appsubmission_posts_columns', 'fo_modify_submissions_table' );

function fo_modify_submission_table_row( $column_name, $post_id ) {
    switch ($column_name) {
        case 'application_id' :
            $application_id = get_field('application_id', $post_id);
            $application = get_post($application_id);
            $title = "#". $application_id . " " . $application->post_title;
            $link = get_edit_post_link($application_id);
            echo "<a href='$link'>$title</a>";
        break;
        case 'funder_id':
            $funder_id = get_field('funder_id', $post_id);
            echo get_field('funder_company_name', "user_$funder_id");
        break;
        case 'applicant_id':
            $applicant_id = get_field('applicant_id', $post_id);
            $user_data = get_userdata($applicant_id);
            echo $user_data->first_name . " " . $user_data->last_name;
        break;
        case 'status':
            echo get_field('status', $post_id);
        break;
    }
    // return $val;
}
add_filter( 'manage_appsubmission_posts_custom_column', 'fo_modify_submission_table_row', 10, 2 );


// Create/Download CSV of Applications
function fo_download_applications_csv($data, $type = 'applications') {
    $csv = '';
    $columns = array();

    switch ($type) {
        case 'applications':
            $columns = array(
                'company_name' => 'Company',
                'applicant_name' => 'Applicant name',
                'applicant_email' => 'Applicant email',
                'loan_amount' => 'Loan amount',
                'uses_of_loan' => 'Use of loan',
                'company_address' => 'Address',
                'phone' => 'Phone',
                'industry' => 'Industry',
                'years_in_business' => 'Years in business',
                'link_to_website' => 'Website',
                'type_of_incorporation' => 'Type of incorporation',
            );
        break;
    }

    $csv = implode(',', $columns);
    $csv .= "\n";
    foreach ($data as $application) {
        $applicant_id = get_field('applicant_id', $application->ID);
        $applicant_meta = get_userdata($applicant_id);
        $row = array();
        foreach ($columns as $key=>$val) {
            if ($key == 'applicant_name') {
                $row[] = get_field('first_name', "user_$applicant_id") . ' ' . get_field('last_name', "user_$applicant_id");
            } elseif ($key == 'applicant_email') {
                $row[] = get_field('email', "user_$applicant_id");
            } elseif ($key == 'company_address') {
                $address = get_field('company_address', $application->ID, false);
                $format_address = $address['street1'];
                $format_address .= $address['street2'] ? ' ' . $address['street2'] : '';
                $format_address .= ' ' . $address['city'];
                $format_address .= ' ' . $address['state'];
                $format_address .= ' ' . $address['postal_code'];
                $row[] = str_replace(',', '', $format_address);
            } else {
                $row[] = get_field($key, $application->ID);
            }
        }
        $csv .= implode(',', $row);
        $csv .= "\n";
    }

    $filename = "funding-organizer-applications.csv"; // TODO change filename to something directly relevant to user?
    header( 'Content-Type: text/csv' ); // Supply the mime type
    header( 'Content-Disposition: attachment; filename="'.$filename.'"' ); // Supply a file name to save
    header( "Cache-Control: no-cache, must-revalidate" );
    header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Set a date in the past
    echo $csv;
    exit;
}


function fo_render_export_csv_button($button_text = 'Download CSV') {
    $url = $_SERVER['REQUEST_URI'];
    $url .= strpos($url, "?") ? "&export=csv" : "?export=csv";
    // echo "<div class=\"mb-3\">";
    echo "<a class=\"btn btn-outline-secondary btn-sm\" href=\"" . $url ."\">" . $button_text . "</a>";
    // echo "</div>";
}


function fo_render_filter_applications_buttons($type = 'partner') {
    global $post;
    $buttons_html = '<div class="filter-applications-buttons mb-4">'
                . '<span>Filters: </span>'
                . '<div class="btn-group">';

    switch ($type) {
        case 'inviter':
            $buttons = array(
                'pending' => 'Awaiting response',
                'registered' => 'Registered',
                'approved' => 'Approved',
                'rejected' => 'Rejected'
            );
            break;
        default:
            $buttons = array(
                'pending' => 'Awaiting response',
                'approved' => 'Approved',
                'rejected' => 'Rejected'
            );
            break;
    }

    $filter = isset($_GET['filter']) && !empty($_GET['filter']) ? $_GET['filter'] : null;

    foreach($buttons as $key => $value) {
        $class = '';
        if ($filter == $key) $class = 'active';
        elseif (!$filter && $key == 'pending') $class = 'active';

        $buttons_html .= "<a href='". get_permalink($post->ID)."?filter=$key' class='btn btn-sm btn-outline-secondary $class'>$value</a>";
    }

    $buttons_html .= '</div></div>';

    echo $buttons_html;
}


// When saving an ACF repeater field, delete any empty rows
// and delete any "deleted" files
function fo_delete_empty_repeater_rows( $value, $post_id, $field, $original ) {
    // get any subfield keys that are file uploads
    // TODO may need to address this further for any deeper than 1 level of repeaters
    $file_upload_field_ids = array_reduce($field['sub_fields'], function($sub_field) {
        if ($sub_field['type'] == 'file') {
            return $sub_field['key'];
        }
    });

    // get array of old file IDs
    // $old_rows = get_field($field['name'], $post_id);
    // $old_file_ids = array();
    // foreach ($old_rows as $old_row) {
    //     foreach ($old_row as $sub_key => $sub_value) {
    //         // if this is a file, add ID to list
    //         if (isset($sub_value['filename'])) {
    //             $old_file_ids[] = $sub_value['ID'];
    //         }
    //     }
    // }

    $updated_file_ids = array();

    // loop thru the rows data
    foreach ($value as $row_key => $row_data) {
        $row_has_value = false;
        foreach ($row_data as $field_key => $field_value) {
            // if any of the subfields have a value, we will save
            if ($field_value) {
                $row_has_value = true;
            }

            // check if the subfield is a file, if so save file ID
            if (in_array($field_key, $file_upload_field_ids)) {
                $updated_file_ids[] = $field_value;
            }
        }

        // if no row had any value, we delete the row
        if (!$row_has_value) {
            unset($value[$row_key]);
        }
    }

    // compare file IDs, delete files that are missing
    // $files_to_delete = array_diff($old_file_ids, $updated_file_ids);

    return $value;
}

// Apply to textarea fields.
add_filter('acf/update_value/type=repeater', 'fo_delete_empty_repeater_rows', 1, 4);


// Add shortcode to get the Credit Release Form link
function fo_get_credit_release_form_link() {
    return get_stylesheet_directory_uri() . '/files/credit-release-authorization.pdf';
}
add_shortcode('credit-release-link', 'fo_get_credit_release_form_link');


// Prefill Need Help form fields
add_filter('acf/prepare_field/name=help_page', 'fo_prefill_help_page', 10, 1);
function fo_prefill_help_page($field)
{
    $field['value'] = site_url($_SERVER['REQUEST_URI']);
    return $field;
}
add_filter('acf/prepare_field/name=help_userid', 'fo_prefill_help_userid', 10, 1);
function fo_prefill_help_userid($field)
{
    global $current_user;
    $field['value'] = $current_user->ID;
    return $field;
}
add_filter('acf/prepare_field/name=help_email', 'fo_prefill_help_email', 10, 1);
function fo_prefill_help_email($field)
{
    global $current_user;
    $field['value'] = $current_user->user_email;
    return $field;
}

// Modify Advanced Forms email notification
function fo_filter_email_message( $content, $email, $form, $fields ) {
	// Add some extra text to the end of the content

    // TODO add link to specific Entry
    if (preg_match('(@darngood.io|@kramercommunications.com|@fundingorganizer.com)', $email['recipient_custom']) === 1) {}

    return $content;
}
// add_filter( 'af/form/email/content/key=form_608c555daf1f0', 'fo_filter_email_message', 10, 4 ); // Need help form



// US coverage area - if "all states" selected, only show that
function fo_load_geography_coverage_value( $value, $post_id, $field ) {
    if(count($value) > 1 && in_array("All states", $value)) {
        // change the value being loaded
        $value = array("All states");

        // update in the database
        update_field('field_5ec6a88c7f717', $value, $post_id);
    }
    return $value;
}
add_filter('acf/load_value/key=field_5ec6a88c7f717', 'fo_load_geography_coverage_value', 10, 3); // US coverage area


// Theme My Login overrides / TML
function fo_modify_login_messages($output)
{
    return $output;
}
// TODO add bootstrap classes to login errors
// add_filter('tml_after_form', 'fo_modify_login_messages');
// add_filter('login_errors', 'fo_modify_login_messages', 20, 1);


function fo_acf_update_value( $value, $post_id, $field, $original ) {
    if( is_string($value) ) {
        $value2 = str_replace( 'Old Company Name', 'New Company Name',  $value );
    }
    return $value;
}
// add_filter('acf/update_value/type=file', 'fo_acf_update_value', 10, 4);


function fo_save_application_delete_files($post_id, $post)
{
    // wp_schedule_event( time(), 'once', 'fo_handle_deleted_application_uploads_cron' );
    // trigger cron job
    fo_handle_deleted_application_uploads($post_id);
}
add_action('save_post_loanapplication', 'fo_save_application_delete_files', 10, 2);

function fo_handle_deleted_application_uploads($post_id)
{
    global $wpdb;
    $deleted_ids = array();

    // Get all of the CURRENT files upload IDs
    $sql = "SELECT meta_value FROM {$wpdb->prefix}postmeta
            WHERE post_id = $post_id
            AND meta_key LIKE '%_file'
            AND meta_key NOT LIKE '\_%'";
    $current_upload_ids = $wpdb->get_col($sql);

    // Get all attachements with $post_id parent
    $args = array(
        'numberposts' => -1,
        'post_type' => 'attachment',
        'post_parent' => $post_id
    );
    $previous_uploads = get_posts($args);
    $previous_upload_ids = array_map(function($file) {
        return $file->ID;
    }, $previous_uploads);

    // determine which media IDs should be removed
    $media_to_delete = array_diff($previous_upload_ids, $current_upload_ids);

    foreach ($media_to_delete as $media_id) {
        $deleted = wp_delete_attachment($media_id);

        if ($deleted) {
            $deleted_ids[] = $deleted->ID;
        }
    }

    // save a list of deleted IDs
    update_field('deleted_file_ids', implode('|', $deleted_ids));
}

// add_action( 'fo_handle_deleted_application_uploads_cron', 'fo_handle_deleted_application_uploads' );

// Add page-specific body classes
add_filter( 'body_class','fo_body_classes' );
function fo_body_classes( $classes ) {
    if (FO_LOCAL_ENV) {
        $classes[] = 'local-environment';
    }

    return $classes;
}