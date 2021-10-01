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

get_header();

$container = get_theme_mod( 'understrap_container_type' );
global $current_user;
$application_id = fo_get_active_application();
?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<div class="col-md-10 offset-md-1 content-area" id="primary">

				<main class="site-main" id="main">

            <?php if ($application_id) { ?>

                <?php
                $has_started_submitting = fo_get_submissions(array('application_id' => $application_id), 'funder_id');
                $can_submit_application = fo_can_submit_application($application_id);
                ?>

                    <div class="entry-content">

                    <?php if (!fo_is_profile_complete()) { ?>
                        <div class="alert alert-warning">
                            <p>
                                Before you submit your application, make sure you finish updating your profile with
                                your first and last name.
                            </p>
                            <a href="<?php echo get_permalink(77); ?>" class="btn btn-primary">Edit Profile</a>
                        </div>
                    <?php } // endif ?>

                        <p>
                            There are three steps you are going to go through to provide funders with the
                            information they need to evaluate you for funding:
                        </p>

                        <div class="card mb-5">
                            <div class="card-header">
                                <h2>Step 1</h2>
                            </div>
                            <div class="card-body">
                                <p class="card-title h5">Gather Documents</p>
                                <p>
                                    Before you begin, click below to see a <strong>Complete List of Documents</strong>
                                    that you will need to complete the Application. Gather these ahead of time to expedite
                                    the process.
                                </p>
                                <p>
                                    Some documents in the list may not apply to you, for example "Copies of Patents/Trademarks"
                                    and "Partnerships Agreements". Don’t worry about them.
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-outline-primary" href="<?php echo get_permalink(575); ?>">List of Required Documents</a>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-5">
                            <div class="card-header">
                                <h2>Step 2</h2>
                            </div>
                            <div class="card-body">
                                <p class="card-title h5">Complete Your Application</p>
                                <p>
                                    Click below to edit your Application. Any questions that do not pertain to your
                                    business should be skipped. Remember to SAVE after you've made changes to an Application page.
                                </p>
                                <p>
                                    If you get interrupted or need to take a break you can sign back in and go to any section
                                    of the application to continue where you left off.
                                </p>
                                <div class="text-center">
                                    <a class="btn btn-outline-primary" href="<?php echo get_permalink(47); ?>">Edit Your Application</a>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h2>Step 3</h2>
                            </div>
                            <div class="card-body">
                                <p class="card-title h5">Submit Your Application</p>
                                <p>Lastly, Review and Submit your Application.</p>
                                <p>Remember, you can fix or change anything anytime. Even after you have submitted.</p>
                            <?php if (!$can_submit_application) { ?>
                                <div class="alert alert-warning">
                                    <p>Before you can submit your Application, you must complete a <a href="<?php echo site_url('loan-application/credit-release'); ?>">Credit Release Authorization</a>.</p>
                                </div>
                            <?php } ?>

                                <div class="text-center">
                                    <a class="btn btn-outline-primary mr-4" href="<?php echo get_permalink($application_id); ?>">Review Application</a>
                                <?php if ($can_submit_application) { ?>
                                    <a class="btn btn-outline-primary" href="<?php echo get_permalink(536); ?>">Submit to Funders</a>
                                <?php } else { ?>
                                    <a class="btn btn-outline-primary disabled">Submit to Funders</a>
                                <?php } ?>
                                </div>
                            </div>
                        </div>

                    <?php if ($has_started_submitting) { ?>

                        <div class="card mt-5">
                            <div class="card-header">
                                <h2>Step 4</h2>
                            </div>
                            <div class="card-body">
                                <p class="card-title h5">View Funder Reponses</p>
                                <p>Congratulations! You have started submitting your Application to potential Funders.</p>
                                <p>You can check back here to review responses.</p>
                                <div class="text-center">
                                    <a class="btn btn-outline-primary" href="<?php echo site_url('my-responses'); ?>">View Funder Responses</a>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

            <?php } else { ?>

                <header class="entry-header">
                    <h1 class="entry-title">Start Your Application</h1>
                </header>
                <div class="entry-content">
                    <p>To begin your application, first enter your Company’s Name. You will then be able to start filling out the remaining form fields of your application.</p>
                    <?php advanced_form('form_5e2f4a60a1fbb'); ?>
                </div>

            <?php } // end if ?>

				</main><!-- #main -->

			</div> <!-- #primary -->

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
