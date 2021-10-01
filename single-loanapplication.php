<?php
/**
 *
 * Template for displaying a Loan Application.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$container = get_theme_mod( 'understrap_container_type' );

global $current_user;
$user_id = $current_user->ID;
$application_id = $post->ID;

// determine if user has access
$access_type = fo_user_has_access_to_application($application_id);
?>

<div class="wrapper" id="page-wrapper">
<?php /*<div class="wrapper" id="full-width-page-wrapper">*/ ?>

	<div class="<?php echo esc_attr( $container ); ?>" id="content">

		<?php fo_render_page_header(); ?>

		<div class="row">

			<?php fo_get_sidebar_by_user_role('application-toc'); ?>

			<div class="col-md content-area" id="primary">
			<?php /*<div class="col-lg-10 offset-md-1 content-area" id="primary">*/ ?>

				<main class="site-main" id="main" role="main">

					<?php if (isset($_GET['result']) && $_GET['result'] == 'approve') { ?>
						<div class="alert alert-success">You have successfully approved this application.</div>
					<?php } elseif (isset($_GET['result']) && $_GET['result'] == 'reject') { ?>
						<div class="alert alert-warning">You have rejected this application.</div>
					<?php } ?>

					<?php if ($access_type != false) : ?>
						<?php while ( have_posts() ) : the_post(); ?>

							<div class="display-fieldgroup my-4 pb-4">
								<h1>
									<span class="h2"><?php echo __("Loan Application for:", 'understrap-child'); ?></span><br>
									<?php the_title(); ?>
								</h1>

							<?php if ($access_type == 'admin' || $access_type == 'funder') { ?>
								<div class="alert alert-warning">
									<p>Download a ZIP of this application and all applicant-provided materials.</p>
									<a class="btn btn-sm btn-primary" target="_blank" href="<?php echo fo_bulk_download_application_link($application_id); ?>">Download Application</a>

									<?php /* ?>
									<a class="btn btn-sm btn-secondary ml-3" target="_blank" href="<?php echo fo_print_application_link($application_id); ?>">Print Application</a>
									<?php //*/ ?>
								</div>
							<?php } ?>

							<?php if ($access_type == 'applicant') { ?>
								<div class="alert alert-warning">
									Below is what Funders will see when reviewing your application.
								</div>
							<?php } elseif ($access_type == 'funder') { ?>
								<?php
								fo_render_approve_reject_buttons($application_id, $review_needed);
								?>
							<?php } ?>
							</div>

							<?php echo fo_get_application_html($post->ID); ?>

						<?php endwhile; // end of the loop. ?>
					<?php else : ?>
						<p>You do not have access to view this application.</p>
					<?php endif; ?>

				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #full-width-page-wrapper -->

<?php get_footer(); ?>
