<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.quantcast.com
 * @since      1.0.0
 *
 * @package    QC_Choice
 * @subpackage QC_Choice/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php get_header(); ?>

<div id="primary" class="content-area">
	<main id="qc_choice_consent_display" class="page-content">
		<div class="centered-content">
			<h2 class="content-title consent-header">
				<?php _e( "Given Consent", "qc-choice" ); ?>
				<a id="qc_choice_consent_change" class="change-consent"><?php _e( "Change consent", "qc-choice" ); ?></a>
			</h2>
			<section class="consent-lists">
				<div class="consent-list-container">
					<table class="consent-list">
						<thead class="consent-list-head">
							<tr class="consent-row">
								<th class="consent-cell">
									<h5 class="consent-list-title"><?php _e( "Vendors", "qc-choice" ); ?></h5>
								</th>
							</tr>
						</thead>
						<tbody id="vendorList">
							<tr class="consent-row">
								<td class="consent-cell"><?php _e( "No consent has been given", "qc-choice" ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="consent-list-container">
					<table class="consent-list">
						<thead class="consent-list-head">
							<tr class="consent-row">
								<th class="consent-cell">
									<h5 class="consent-list-title"><?php _e( "Vendor Purposes", "qc-choice" ); ?></h5>
								</th>
							</tr>
						</thead>
						<tbody id="vendorPurposeList">
							<tr class="consent-row">
								<td class="consent-cell"><?php _e( "No consent has been given", "qc-choice" ); ?></td>
							</tr>
						</tbody>
					</table>
					<table class="consent-list">
						<thead class="consent-list-head">
							<tr class="consent-row">
								<th class="consent-cell">
									<h5 class="consent-list-title"><?php _e( "Publisher Purposes", "qc-choice" ); ?></h5>
								</th>
							</tr>
						</thead>
						<tbody id="publisherPurposeList">
							<tr class="consent-row">
								<td class="consent-cell"><?php _e( "No consent has been given", "qc-choice" ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>
