<?php
/**
 * *
 * @package understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<?php fo_render_page_header(); ?>

		<div class="row">

			<!-- Do the left sidebar check -->
			<?php //get_template_part( 'global-templates/left-sidebar-check' ); ?>
			<?php //fo_get_sidebar_by_user_role() ?>


			<div class="col-lg-10 offset-md-1 content-area" id="primary">

				<main class="site-main" id="main">


					<?php while ( have_posts() ) : the_post(); ?>

					<header class="entry-header mb-4">
						<h1 class="entry-title">List of Documents</h1>
					</header>

					<div class="entry-content">
						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Documents on Company Assets</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>
										A list of assets that have a value of $10,000 or greater, such as land, buildings,
										equipment, collectibles, cars, trucks, and copies of appraisals by a third party.
									</li>
								</ul>
							</div>
						</div>
						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Business Records</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>Accounts Receivable</li>
									<li>Accounts Payable</li>
									<li>Balance Sheet</li>
									<li>List of Clients and Revenue per Client</li>
									<li>Sample of Client Agreement</li>
								</ul>
							</div>
						</div>
						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Business Plans</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>Marketing Plans</li>
									<li>Sales Plans</li>
									<li>Documents on Competitive Analysis</li>
								</ul>
							</div>
						</div>
						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Bank Statements</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>3 months of Corporate Bank Statements</li>
								</ul>
							</div>
						</div>
						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Bios</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>Bios of Stockholders with ownership of 20% or more</li>
									<li>Bios of Top Management</li>
									<li>Bios of Board of Directors</li>
								</ul>
							</div>
						</div>
						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Tax Returns</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>3 years of Federal Tax Returns</li>
									<li>3 years of State Tax Returns</li>
								</ul>
							</div>
						</div>

						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Personal Financial Information</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>3-Years of Personal Tax Returns for each person that owns over 20% of the company.</li>
									<li>List of any real estate owned by each person that owns over 20% of the company such as person homes, vacation homes or plots of land.</li>
									<li>Brokerage and bank statements that are no more than 30 days old.</li>
									<li>2 Forms of ID- Back and front of a driver’s license and copy of the password for each person that owns 20% or more of the company.</li>
									<li>3-Months of Personal Bank Statements for each person with 20% or more ownership. (Under Bank Statements on the List of Documents.  Please remove 3-Months of Person Bank Statements. Since will list it under Personal Financial Information.</li>
									<li>Copies of all Loans/Mortgages and most recent account statement(s).</li>
								</ul>
							</div>
						</div>

						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Corporate Real Estate Assets</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>List of real estate owned by corporation or subsidiaries.</li>
									<li>Copy of most recent Environmental Report Phase One and Two.</li>
									<li>Copies of all Loans/Mortgages and most recent account statement(s).</li>
								</ul>
							</div>
						</div>

						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Machinery &amp; Equipment Appraisal or Valuation</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>List of business owned equipment.</li>
									<li>Most recent Business Valuation of the machinery and equipment.</li>
									<li>Machinery &amp; Equipment: Copies of documents and or agreements evidencing other material financing agreements, including sale and leaseback arrangements and installment purchases.</li>
								</ul>
							</div>
						</div>

						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Affiliated Companies</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>List of Affiliated Companies where the stockholders of the company requesting the loan own more than 20% of the Affiliated Company.  Example: A restaurant owner is borrowing money for her restaurant called Mary’s, Inc.  Mary has a catering business called “Mary’s Catering Inc.”  The applicant needs to list the ownership that Mary’s Inc. has in Mary’s Catering Inc and how much of the two companies the applicant owns.</li>
								</ul>
							</div>
						</div>

						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Working Capital Facilities</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>Copies of line of credit security agreement. Including any amendments, renewal letters, notices, default waivers or other related documents.</li>
									<li>Copies of promissory note. Including any amendments, renewal letters, notices, default waivers or other related documents.</li>
									<li>Copies of factoring agreement. Including any amendments, renewal letters, notices, default waivers or other related documents.</li>
									<li>Copies of Merchant Cash Advance Loans (‘MCA’s) including any amendments, renewal letters, notices, default waivers or other related documents.</li>
								</ul>
							</div>
						</div>

						<div class="row mb-4">
							<div class="col-4">
								<p class="text-uppercase">Other Assets</p>
							</div>
							<div class="col-8">
								<ul class="mb-0">
									<li>Inventory</li>
									<li>Patents pending</li>
									<li>Credit Release Authorization Letter for lender to communicate with accounting firm, bank, attorney.</li>
									<li>Copies of Patents, Trademarks, etc.;</li>
									<li>Copies of Shares of other private companies owned by the applicant;</li>
									<li>Information (via spreadsheet) on the applicant's income from external sources such as a second business, real estate, trust fund, et. al.</li>
									<li>Copies of Lawsuits the business or the applicant was involved in.</li>
									<li>Copy of driver’s license.</li>
								</ul>
							</div>
						</div>


					</div>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->

			</div> <!-- #primary -->

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php get_footer(); ?>
