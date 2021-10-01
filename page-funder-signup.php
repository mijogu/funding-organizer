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
// $user_id = $current_user->ID;
// $application_id = $post->ID;

// // determine if user has access
// $access_type = fo_user_has_access_to_application($application_id);
?>

<div class="wrapper" id="full-width-page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content">

		<div class="row">

			<div class="col-md-12 content-area" id="primary">

				<main class="site-main narrow-form" id="main" role="main">

					<?php /*if ($access_type == 'funder' || $access_type == 'admin'): ?>
						<a href="/review-applications"><?php echo __('&laquo; Back to Review Applications', 'understrap-child'); ?></a>
					<?php endif;*/ ?>
					
					<?php //if ($access_type != false) : ?>
						<?php while ( have_posts() ) : the_post(); ?>

							<div class="display-fieldgroup my-4 pb-4">
								<h1><?php the_title(); ?></h1>
							</div>
							
							<?php  
							// ensure user is not logged in?
							$exclude_fields = fo_get_excluded_fields_by_page('funder-signup');
							$args = array(
								'exclude_fields' => $exclude_fields,
								'user' => 'new'
							);						
							advanced_form('form_5ec6a55b507c4', $args);
							?>

						<?php endwhile; // end of the loop. ?>
					<?php /*else : ?>
						<p>You do not have access to view this application.</p>
					<?php endif;*/ ?>

				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .row end -->

	</div><!-- #content -->

</div><!-- #full-width-page-wrapper -->

<?php get_footer(); ?>
