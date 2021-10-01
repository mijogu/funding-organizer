<?php
/**
 *
 * This template is used to show Funder Responses to the Applicant
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user;
$user_id = $current_user->ID;
$application_id = get_field('loan_application_id', "user_$user_id");

// redirect user if not an Applicant
if (!$application_id) {
    wp_redirect('/');
}

$post_per_page = 30;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
    'posts_per_page' => $post_per_page,
    'paged' => $paged,
    'post_type' => 'appsubmission',
    'meta_key' => 'application_id',
    'meta_value' => $application_id
);

// get all submissions for application
// $submissions = fo_get_submissions(array('application_id' => $application_id));
$submissions = new WP_Query($args);

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">

	<?php /*<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">*/ ?>
	<div class="container" id="content" tabindex="-1">

        <?php fo_render_page_header(); ?>

    </div>

    <div class="container">
        <div class="row">

			<?php //<div class="col-lg-10 offset-md-1 content-area" id="primary"> ?>
			<div class="content-area col" id="primary">

                <header class="entry-header">
                    <?php //the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                <?php the_content(); ?>

                <p>
                    Below is a list of all of the Funders that your Application has been submitted to.
                    They will contact you directly to discuss further.
                </p>

                <p>To submit your Application to more Funders, go to <a href="<?php echo site_url('submit-application') ?>">Submit Application</a>.</p>

                <?php

            // if (count($submissions) > 0) {
            if ($submissions->found_posts > 0) {
                echo '<table class="table table-striped table-bordered">'
                    .'<thead><tr>'
                    .'<th>Date submitted</th>'
                    .'<th>Funder</th>'
                    .'<th>Response</th>'
                    .'</tr></thead>';

                while ($submissions->have_posts()) {
                    $submissions->the_post();

                    // foreach($submissions as $submission) {
                    $funder_id = get_field('funder_id', $post->ID);
                    echo '<tr>';
                    echo '<td>' . get_the_date('', $post->ID) . '</td>';
                    echo '<td>' . get_field('funder_company_name', "user_$funder_id") .'</td>';
                    echo '<td>' . get_field('status', $post->ID) . '</td>';
                    echo '</tr>';
                } // endwhile
                echo '</table>';
            } // endif

            if (function_exists("fo_display_pagination")) {
                fo_display_pagination($submissions->max_num_pages);
            }

            wp_reset_postdata();
            ?>
            </div> <!-- #primary -->
        </div><!-- .row -->
    </div><!-- .container -->
</div><!-- #page-wrapper -->

<?php get_footer(); ?>