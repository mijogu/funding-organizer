<?php
/**
 * Template Name: Application Form Template
 *
 * This template is used to display the appropriate Application Form
 * and determines whether or not a new Application Form is needed
 * or if the user already has one started.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $wp;
global $current_user;

if (!is_user_logged_in()) {
	wp_redirect('/');
}

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<?php fo_render_page_header(); ?>

		<div class="row">

			<?php //fo_get_sidebar_by_user_role() ?>
			<div class="col-md-4 widget-area" id="left-sidebar" role="complementary">
				<?php dynamic_sidebar( 'edit-application-sidebar' ); ?>
            </div>

			<div class="col-md content-area" id="primary">

				<main class="site-main" id="main">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php if ($post->post_name != 'credit-release') { ?>
							<p>Please fill out only the questions that are applicable to your business.  The Funders will let you know if the section you left blank is information they need.</p>
						<?php } ?>

						<?php get_template_part( 'loop-templates/content', 'page' ); ?>

						<?php if (isset($_GET['submission']) && $_GET['submission'] == 'success') { ?>
							<div class="alert alert-warning">Your application has been saved!</div>
						<?php }

						$url = $_SERVER['REQUEST_URI'];
						$param = "submission=success";
						$url .= strpos($url, '?') ? "&".$param : "?".$param;
						$args = array(
							'redirect' => $url,
							'filter_mode' => true,
							'uploader' => 'basic'
						);
						fo_show_application_form($post->post_name, $args);
						?>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->

			</div> <!-- #primary -->

			<!-- Do the right sidebar check -->
			<?php //get_template_part( 'global-templates/right-sidebar-check' ); ?>

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
