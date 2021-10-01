<?php
/**
 *
 * Template for displaying the Edit Profile page
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

			<div class="col-lg-10 offset-md-1 content-area" id="primary">

				<main class="site-main" id="main" role="main">

					<?php //tml_2fa_add_to_wp_profile($current_user); ?>
					
					<?php //if ($access_type != false) : ?>
						<?php while ( have_posts() ) : the_post(); ?>

							<div class="display-fieldgroup my-4 pb-4">
								<h1><?php the_title(); ?></h1>
							</div>
							

							<?php  
							if (af_has_submission()) { ?>
								<div class="alert alert-success">Your profile has been saved!</div>
							<?php }

							if (in_array('funder', $current_user->roles) && get_field('partner_funder_status', "user_$current_user->ID") == 'approved') {
								
								$exclude_fields = fo_get_excluded_fields_by_page('partner-profile');
								$args = array(
									'exclude_fields' => $exclude_fields,
									'user' => $current_user->ID,
									'filter_mode' => true
								);						
								advanced_form('form_5ec6b9b74209e', $args);

							} elseif (in_array('funder', $current_user->roles)) { // Funder
								
								$exclude_fields = fo_get_excluded_fields_by_page('funder-profile');
								$args = array(
									'exclude_fields' => $exclude_fields,
									'user' => $current_user->ID,
									'filter_mode' => true
								);						
								advanced_form('form_5ec6a55b507c4', $args);

							} else {

								$exclude_fields = fo_get_excluded_fields_by_page('applicant-profile');
								$args = array(
									'exclude_fields' => $exclude_fields,
									'user' => $current_user->ID,
									'filter_mode' => true
								);						
								advanced_form('form_5ec957af1586f', $args);

							}

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
