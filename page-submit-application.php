<?php
/**
 *
 * This template is used to display the appropriate Application Form
 * and determines whether or not a new Application Form is needed
 * or if the user already has one started.
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $current_user;
$user_id = $current_user->ID;
$application_id = get_field('loan_application_id', "user_$user_id");

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
			<div class="content-area" id="primary">

                <header class="entry-header">
                    <?php //the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header><!-- .entry-header -->

                <?php
                // check if applicant is locked to a funder
                $locked_to_funder = get_field('locked_to_funder', "user_$user_id");
                $unlocked_application = get_field('unlocked_application', "user_$user_id");

                // TODO TEST
                $filters = array(
                    'application_id' => $application_id,
                    'funder_id' => $locked_to_funder
                );
                $locked_submission = fo_get_submissions($filters);
                $submitted_to_locked_funder = count($locked_submission) >= 1 ? true : false;
                $funders_submitted_to;
                $show_unlocking_instruction = true;

                $funder_name = get_field('first_name', "user_$locked_to_funder") . ' ' . get_field('last_name', "user_$locked_to_funder");
                $funder_company = get_field('funder_company_name', "user_$locked_to_funder");;

                // applicant is locked to funder, AND has NOT submitted to inviting funder
                if ($locked_to_funder && !$submitted_to_locked_funder) { // also should still be locked ?>
                    <p>You were invited to Funding Organizer by:</p>

                    <p><span class="alert alert-info">
                        <strong><?php echo $funder_name; ?></strong>
                        from
                        <strong><?php echo $funder_company; ?></strong>
                    </span></p>

                    <p>
                        When you are ready to submit your application, click the button below and
                        they will notified that your application has been submitted.
                    </p>

                    <span class="locked-application">
                        <?php advanced_form('form_5e39b93ada6ac'); ?>
                    </span>
                    <?php

                }
                // applicant is locked, BUT HAS submitted to inviting funder
                elseif ($locked_to_funder && $submitted_to_locked_funder && !$unlocked_application) { ?>

                    <p>Congratulations, you have submitted your application to:</p>

                    <p><span class="alert alert-info">
                        <strong><?php echo $funder_name; ?></strong>
                        from
                        <strong><?php echo $funder_company; ?></strong>
                    </span></p>

                    <p>They will contact you about the status of your application.</p>

                    <?php
                    // TO DO add a time delay to show instructions on how to unlock application
                    /*
                    if ($show_unlocking_instruction) { ?>

                    <p>
                        In the event that your application is rejected by the funder who invited you to use
                        this platform, you have the option to contact us about unlocking your application which
                        would allow you to submit to some of our Partner Funders.
                    </p>

                    <p>
                        For information about unlocking your application, please send an email to {email address}
                        with the subject "Unlock my Application".
                    </p>
                    <?php }
                    */
                }
                // application is unlocked
                elseif ($locked_to_funder && $unlocked_application) { ?>

                    <p class="alert alert-info">
                        Great news! Your application has been unlocked. You may now choose to submit your application
                        to some of our Partner Funders.
                    </p>

                <?php } ?>
            </div> <!-- #primary -->
        </div><!-- .row -->
    </div><!-- .container -->
    <div class="container">
        <div class="row">

            <?php
            // applicant was never locked, OR has been unlocked
            if (!$locked_to_funder || $unlocked_application) {

                // show list of banks already submitted to
                $funder_list = fo_funders_submitted_to_list($application_id);

                if ($funder_list != null) { ?>

                    <p>You previously submitted this application to the following funders.</p>

                    <?php echo $funder_list; ?>

                    <p>To view the status of those submissions, go to <a href="<?php echo site_url('my-responses'); ?>">Funder Responses</a>.</p>

                <?php }


                // check if already submitted
                $num_funders_available = fo_get_available_funders($application_id, true);
                if ($num_funders_available > 0) {

                    // show form but only with the options of the banks that haven't been submitted to
                    advanced_form('form_5e39b93ada6ac', array('filter_mode' => true));

                } else { ?>

                        <p>There are no more funders for you to submit to at this time.</p>

                <?php }

            } ?>

		</div><!-- .row -->

	</div><!-- .container-fluid -->

</div><!-- #page-wrapper -->

<?php /*
<div class="modal fade" id="confirm-submit" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                Confirm Application Submission
            </div>
            <div class="modal-body">
                Are you sure you are ready to submit your application? This cannot be undone.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No, not yet</button>
                <a href="" id="confirm-submit-application" class="btn btn-success">Yes, submit!</a>
            </div>
        </div>
    </div>
</div>
*/ ?>

<?php get_footer(); ?>