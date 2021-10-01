<?php
/**
 * Template Name: Manage Applications
 *
 * Template for displaying all available Loan Applications for a Funder.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$container = get_theme_mod( 'understrap_container_type' );

global $current_user;
$user_id = $current_user->ID;

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$applications = null;
$has_applications_to_review = true;
$args = array();

// if admin, include all Applications
// if funder, only show only those with access to
$is_admin = in_array('administrator', (array) $current_user->roles);
$is_funder = in_array('funder', (array) $current_user->roles);
$is_funder_approved = fo_is_funder_approved($user_id);

?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content">

		<div class="row">

            <?php fo_get_sidebar_by_user_role(); ?>

			<div class="col-md content-area" id="primary">

				<main class="site-main" id="main" role="main">

                <?php if ($is_funder_approved) { ?>

                    <header class="entry-header">
                        <?php //the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                        <h1 class="entry-title">Overview</h1>
                    </header><!-- .entry-header -->

                    <?php the_content(); ?>

                    <p>Instruct applicants to signup with Funding Organizer with your unique link.</p>

                    <div class="alert alert-warning"><?php echo fo_get_funder_invite_link(); ?></div>

                <?php } else { ?>

                    <h1>Welcome to Funding Organizer</h1>

                    <p>
                    Thank you for your interest in Funding Organizer. Your account is still pending approval and will be reviewed soon.
                    You can expect an email notification when your account has been approved.
                    </p>

                <?php } ?>
				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #full-width-page-wrapper -->

<?php get_footer(); ?>
