<?php
/**
 * Template Name: Partner Applications
 *
 * Template for displaying all available Loan Applications for a Funder.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user;
$user_id = $current_user->ID;

$post_per_page = 20;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$applications = null;
$has_applications_to_review = true;

// if admin, include all Applications
// if funder, only show only those with access to
$is_admin = in_array('administrator', (array) $current_user->roles);
$is_funder = in_array('funder', (array) $current_user->roles);
$partner_funder_status = get_field('partner_funder_status', "user_$user_id");

// look at query param, check if we want Approved, Rejected, Signed Up, or To Review
$page_title;
$filter = isset($_GET['filter']) && !empty($_GET['filter']) ? $_GET['filter'] : 'pending';
$export = isset($_GET['export']) && !empty($_GET['export']) ? $_GET['export'] : null;

$args = array(
    'posts_per_page' => $post_per_page,
    'paged' => $paged,
    'post_type' => 'loanapplication',
);

if (!$is_admin && !$is_funder) {
    // applicants shouldnt' see this page
    die();
} elseif ($is_funder) {

    // get all application IDs for given filters
    $filters = array(
        'funder_id' => $user_id,
        'type' => 'partner',
        'status' => $filter
    );
    $application_ids = fo_get_submissions($filters, 'application_id', $post_per_page, $paged);

    if (count($application_ids)) {
		$args['post__in'] = $application_ids;
	} else {
        $has_applications_to_review = false;
    }
}

// remove limit if we're exporting, bc we want all of them
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
                        <h1 class="entry-title">Partner Applicants</h1>
                    </header><!-- .entry-header -->

                    <?php echo $page_content; ?>

                    <?php
                    if ($is_funder && $partner_funder_status == 'pending') { ?>

                        <p>
                            Your application to become a Partner Funder has been received, but is not yet approved.
                            You will receive an email notification when you have been approved.
                        </p>

                    <?php }
                    elseif ($is_funder && $partner_funder_status == 'none') { ?>

                        <p>You have not yet applied to become a Partner Funder.</p>
                        <p>
                            <a href="<?php echo get_permalink(769); ?>">Apply to Become a Partner Funder</a>
                        </p>


                    <?php  }
                    elseif ($has_applications_to_review && $applications->found_posts > 0) { ?>

                        <?php fo_render_filter_applications_buttons(); ?>

                        <?php fo_render_applicants_table($applications); ?>

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

                    <?php }
                    else { ?>

                        <?php fo_render_filter_applications_buttons(); ?>

                        <?php switch ($filter) {
                            case 'pending':
                                echo '<p>There are no Partner Applications for you to review.</p>';
                                break;
                            case 'approved':
                                echo '<p>You have not approved any Partner Applications yet.</p>';
                                break;
                            case 'rejected':
                                echo '<p>You have not rejected any Partner Applications yet.</p>';
                                break;
                        } ?>
                    <?php } ?>
				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
