<?php
/**
 * Email Header
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/yith-ywar-email-header.php.
 *
 * @package YITH\AdvancedReviews\Templates\Emails
 * @var $email_logo
 * @var $email_heading
 * @var $email_logo_align
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo wp_kses_post( get_bloginfo( 'name', 'display' ) ); ?></title>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
<table width="100%" id="outer_wrapper">
	<tr>
		<td><!-- Deliberately empty to support consistent sizing and layout across multiple email clients. --></td>
		<td width="600">
			<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
				<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
					<tr>
						<td align="center" valign="top">
							<div id="template_header_image">
								<?php if ( $email_logo ) : ?>
									<p style="margin-top:0; padding: 0 0 16px 0; text-align:<?php echo esc_attr( $email_logo_align ); ?>">
										<img src="<?php echo esc_url( $email_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" />
									</p>
								<?php endif; ?>
							</div>
							<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container">
								<tr>
									<td align="center" valign="top">
										<!-- Header -->
										<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header">
											<tr>
												<td id="header_wrapper">
													<h1><?php echo esc_html( $email_heading ); ?></h1>
												</td>
											</tr>
										</table>
										<!-- End Header -->
									</td>
								</tr>
								<tr>
									<td align="center" valign="top">
										<!-- Body -->
										<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body">
											<tr>
												<td valign="top" id="body_content">
													<!-- Content -->
													<table border="0" cellpadding="20" cellspacing="0" width="100%">
														<tr>
															<td valign="top">
																<div id="body_content_inner">
