<?php
/**
 *
 * Template Name: Dashboard
 *
 * @package understrap
 */

/// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<!-- Do the left sidebar check -->
			<?php get_template_part( 'global-templates/left-sidebar-check' ); ?>

			<main class="site-main" id="main">


                <article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

                    <header class="entry-header">

                        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                    </header><!-- .entry-header -->

                    <?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>

                    <div class="entry-content">

                        <?php the_content(); ?>

                        <?php
                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
                                'after'  => '</div>',
                            )
                        );
                        ?>

                    </div><!-- .entry-content -->

                    <footer class="entry-footer">

                        <?php edit_post_link( __( 'Edit', 'understrap' ), '<span class="edit-link">', '</span>' ); ?>

                    </footer><!-- .entry-footer -->

                    </article><!-- #post-## -->


			</main><!-- #main -->

			<!-- Do the right sidebar check -->
			<?php get_template_part( 'global-templates/right-sidebar-check' ); ?>

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>