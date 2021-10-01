<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user;

$container = get_theme_mod( 'understrap_container_type' );
?>

<?php if ($current_user->ID) { ?>

	<div class="wrapper" id="wrapper-footer-help">
        <div class="container">
			<div class="col text-center">

			<?php if (isset($_GET['help-submission']) && $_GET['help-submission'] == 'success') { ?>

				<div class="alert alert-success mb-0" id="help-success">
					<p class="font-weight-bold mb-0">We have received your request for help and will reach out as soon as we can.</p>
				</div>

			<?php } else { ?>

                <span class="mr-4">Having trouble? Reach out with any questions.</span>
				<a class="btn btn-outline-primary btn-lg" data-toggle="modal" data-target="#need-help" href="">Get help</a>

			<?php } ?>
            </div>
        </div>
    </div>

    <div id="need-help" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Need help?</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<?php //acfe_form('need-help'); ?>
					<?php
					$url = site_url() . $_SERVER['REQUEST_URI'];
					$pos = strpos($url, '?');
					$url .= ($pos) ? "&" : "?";
					$url .= "help-submission=success#help-success";
					$args = array(
						'redirect' => $url,
						'filter_mode' => true,
						'uploader' => 'basic'
					);
					advanced_form('form_608c555daf1f0', $args);
					?>
				</div>
			</div>
        </div>
	</div>

<?php } ?>

<div class="wrapper" id="wrapper-footer-above">
	<div class="<?php echo esc_attr( $container ); ?>" id="footer-full-content" tabindex="-1">
		<div class="row">
			<?php dynamic_sidebar( 'footerfull' ); ?>
		</div>
	</div>
</div><!-- #wrapper-footer-above -->

<div class="wrapper" id="wrapper-footer">

	<div class="<?php echo esc_attr( $container ); ?>">

		<div class="row">

			<div class="col-md-12">

				<footer class="site-footer" id="colophon">

					<div class="site-info text-center small text-white">

						<p>mkramer@fundingorganizer.com<br>
						484 288-0594<br>
						2601 Pennsylvania Ave., Philadelphia, PA 19130</p>

						<p>Copyright &copy; 2020 by The Funding Organizer, LLC.  All Rights Reserved.</p>

						<?php //understrap_site_info(); ?>

					</div><!-- .site-info -->

				</footer><!-- #colophon -->

			</div><!--col end -->

		</div><!-- row end -->

	</div><!-- container end -->

</div><!-- wrapper end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>

