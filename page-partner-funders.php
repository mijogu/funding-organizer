<?php
/**
 * Template Name: Partner Funders List
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>

<div class="wrapper" id="page-wrapper">

	<div class="container-fluid" tabindex="-1">

		<div class="row">

            <div class="mx-3">

                <?php while ( have_posts() ) : the_post(); ?>

                <header class="entry-header">
                        <?php //the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                    <?php the_content(); ?>

                    <?php fo_render_funder_table(); ?>

                <?php endwhile; // end of the loop. ?>
            
            </div>			

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
