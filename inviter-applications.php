<?php
/**
 * Template Name: Your Applicants
 *
 * Template for displaying all available Loan Applications for a Funder.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user;
$user_id = $current_user->ID;

$posts_per_page = 20;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$applications = null;
$has_applications_to_review = true;

// if admin, include all Applications
// if funder, only show only those with access to
$is_admin = in_array('administrator', (array) $current_user->roles);
$is_funder = in_array('funder', (array) $current_user->roles);

// look at query param, check if we want Approved, Rejected, Signed Up, or To Review
$page_title;
$filter = isset($_GET['filter']) && !empty($_GET['filter']) ? $_GET['filter'] : 'pending';
$export = isset($_GET['export']) && !empty($_GET['export']) ? $_GET['export'] : null;
$link_to = true;

$args = array(
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'post_type' => 'loanapplication',
);

if (!$is_admin && !$is_funder) {
    // applicants shouldnt' see this page
    die();
} elseif ($is_funder) {

    $application_ids = array();

    if ($filter == 'registered') {

        // get all locked
        $application_ids = fo_get_locked_applications_for_funder($user_id);
        $link_to = false;

    } else {

        // get all application IDs for given filters
        $filters = array(
            'funder_id' => $user_id,
            'type' => 'inviter',
            'status' => $filter
        );
        $application_ids = fo_get_submissions($filters, 'application_id', $post_per_page, $paged);

    }

    if ($application_ids) {
        $args['post__in'] = $application_ids;
    } else {
        $has_applications_to_review = false;
    }
}

// remove limit if we're exporting
if ($export) {
    unset($args['posts_per_page']);
    unset($args['paged']);
}

$applications = new WP_Query($args);

// prompt file download if we're exporting
if ($export) {
    fo_download_applications_csv($applications);
}

// End pre-render logic

get_header();
$container = get_theme_mod( 'understrap_container_type' );
?>


<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content">

		<div class="row">

            <?php fo_get_sidebar_by_user_role(); ?>

			<div class="col-md content-area" id="primary">

				<main class="site-main" id="main" role="main">
                    <header class="entry-header">
                        <h1 class="entry-title">Your Applicants</h1>
                    </header><!-- .entry-header -->

                    <?php echo $page_content; ?>

                    <?php if ($has_applications_to_review && $applications->found_posts > 0) : ?>

                        <?php fo_render_filter_applications_buttons('inviter'); ?>

                        <?php fo_render_applicants_table($applications, $link_to); ?>

                        <div class="row">
                            <div class="col">
                                <?php if (function_exists("fo_display_pagination")) {
                                    fo_display_pagination($applications->max_num_pages);
                                }?>
                            </div>
                            <div class="col text-right">
                                <?php fo_render_export_csv_button(); ?>
                            </div>
                        </div>

                    <?php else : ?>

                        <?php fo_render_filter_applications_buttons('inviter'); ?>

                        <?php switch ($filter) {
                            case 'pending':
                                echo '<p>There are no applications for you to review.</p>';
                                break;
                            case 'registered':
                                echo '<p>You do not have any registered applicants with unsubmitted applications.</p>';
                                break;
                            case 'approved':
                                echo '<p>You have not approved any applications yet.</p>';
                                break;
                            case 'rejected':
                                echo '<p>You have not rejected any applications yet.</p>';
                                break;
                        } ?>


                        <?php endif; ?>
				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
