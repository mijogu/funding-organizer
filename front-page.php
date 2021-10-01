<?php
/**
 *
 * Template for displaying a fullwidth frontpage
 *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
$container = get_theme_mod( 'understrap_container_type' );
?>

<div class="wrapper" id="wrapper-hero">
	<div class="jumbotron py-5 jumbotron-fluid text-white homepage-hero">
		<div class="container text-center my-5">
			<div class="row">
				<div class="col-md-10 offset-md-1">
					<?php //<h1 class="display-4 font-weight-bold">Streamline your way to faster execute your business</h1>?>
					<h1 class="display-4 font-weight-bold text-uppercase mb-4">Simplify!</h1>
					<h2 class="h4 text-uppercase font-weight-normal mb-4">We eliminate the time-consuming process of applying for commercial loans</h2>
					<?php /*<a class="btn btn-secondary btn-lg px-5" href="<?php echo site_url('register'); ?>">Start here</a>*/ ?>
				</div>
			</div>
			<div class="row">
				<div class="col text-center">
					<p class="">Funding Organizer provides a comprehensive, efficient and secure process for providing and collecting commercial loan applications, keep existing clients information updated and connect applicants with funders.</p>
					<p><em>We are <u>not</u> loan brokers and don’t take any commissions.</em></p>
				</div>
			</div>
		</div>
	</div>
	<div class="container text-center my-5">
		<div class="row">
			<div class="col py-5">
				<h2 class="">A FIRST OF ITS KIND</h2>
				<p>An online, cloud-based, shareable, applicant-controlled and password-protected system
				that saves both loan applicants and funding sources much valued time and resources!</p>
				<p>SEE HOW FUNDING ORGANIZER CAN WORK FOR YOU!</p>

			</div>
		</div>
	</div>
</div>

<?php /*
<div class="d-lg-flex py-3 px-5 bg-secondary text-white col-10 offset-1 offset-lg-0 col-lg-10 text-center text-lg-left hero-overlap">
	<div class="flex-lg-fill mb-2 mb-lg-0">
		<span class="d-inline d-lg-block">Business Loans from</span>
		<span class="h4">$3,000 - $3 million</span>
	</div>
	<div class="flex-lg-fill mb-2 mb-lg-0">
		<span class="d-inline d-lg-block">Rates as low as</span>
		<span class="h4">4.5%</span>
	</div>
	<div class="flex-lg-fill mb-2 mb-lg-0">
		<span class="d-inline d-lg-block">Get qualified in</span>
		<span class="h4">5 minutes</span>
	</div>
	<div class="flex-lg-fill mb-2 mb-lg-0">
		<span class="d-inline d-lg-block">Choose terms</span>
		<span class="h4">6, 12, or 18 months</span>
	</div>
</div>
*/ ?>


<div class="" id="full-width-page-wrapper">

	<div class="container py-5" id="content">

		<div class="row">
			<div class="col-12 col-md-4 d-flex flex-column mb-5 mb-md-0">
				<h3 class="h5 text-uppercase">Loan Applicants</h3>
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/loan-applicant2.jpg" class="my-3">
				<p>Have the list of documents required all at once, then apply for a loan in one click -- to as many funding
				sources as you like.</p>
				<span class="mt-auto"><a href="/register/" class="btn btn-primary">Register now!</a></span>
			</div>
			<div class="col-12 col-md-4 d-flex flex-column mb-5 mb-md-0">
				<h3 class="h5 text-uppercase">Funders</h3>
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/funder3.jpg" class="my-3">
				<p>Enhance your reputation! Improve your customer service and employee performance by making it simple for your
				clients to apply, to collect applicant information and keep information from existing customers for the annual
				financial information for annual reviews which are regulatory requirements.</p>
				<span class="mt-auto"><a href="/funder-signup/" class="btn btn-primary">Register now!</a></span>
			</div>
			<div class="col-12 col-md-4 d-flex flex-column mb-5 mb-md-0">
				<h3 class="h5 text-uppercase">Funding Partners</h3>
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/img/funding-partners.jpg" class="my-3">
				<p>Receive fully-completed FUNDING ORGANIZER loan applications without soliciting nor collecting client information.</p>
				<span class="mt-auto"><a href="/partner-funder-signup/" class="btn btn-primary">Register now!</a></span>
			</div>
			<?php /*
			<div class="col col-md-6">
				<h3 class="h5 text-teal">To Business Owner</h3>
				<p>Funding Organizer, a secure online privacy protected, common application to apply for funding from a
				bank, savings and loan, credit union, factor, loan broker or private investor. No more receiving a blizzard
				of emails over months requesting an assortment of information that takes you, your employees and your
				accountant away from helping you make money and serving your customers.  Our service was designed to make it
				easier and more efficient to apply for funding by providing almost every conceivable question a funder would
				want answers along with why they need it.</p>
				<p><strong>No where will you be asked for Social Security Numbers or corporate
				tax numbers and only you have the power to share the information.</strong>  If you don’t have any funding relationships,
				we can make an introduction and we don’t take a percentage of any loans that you obtain from working with us. 
				Just the fee to use the service.</p>
				<?php /*<p>By providing a secure online privacy protected common funding request application that asks all of the question’s banks need answers for in order to evaluate the loan worthiness of a business in as fast as 5 minutes. Plus, we have relationships with banks, and you can choose the banks you are interested in speaking with.</p>* ?>
			</div>
			<div class="col col-md-6">
				<h3 class="h5 text-teal">To Funders</h3>
				<p>By providing an online secure privacy protected loan application that saves you time and money from having to
				send requests for information along with the follow up required to obtain it. We can private label this service
				for your institution or you can just send the applicant a link to our site and they can let you know when the
				information is available to be reviewed. This will allow you to service and evaluate more applicants in a faster
				more efficient way.</p>
			</div>
			*/ ?>
		</div>

	</div><!-- #content -->

	<div class="container py-5 text-center">
		<a href="<?php echo get_permalink(928); ?>" class="h5">Click here for a full list of our Partners &raquo;</a>
	</div>

</div><!-- #full-width-page-wrapper -->

<?php get_footer(); ?>
