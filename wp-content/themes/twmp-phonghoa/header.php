<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Font Google -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-W885V6JK');</script>
	<!-- End Google Tag Manager -->
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<!-- Google Tag Manager (noscript) -->
	<noscript>
	<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W885V6JK"
	height="0" width="0" style="display:none;visibility:hidden"></iframe>
	</noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php wp_body_open(); ?>
	<header data-block="header-main" id="header-sticky">
		<?php
		if (!is_front_page() && is_active_sidebar('header-top')) {
		?>
			<div class="header__top">
				<div class="container header__container">
					<?php dynamic_sidebar('header-top'); ?>
				</div>
			</div><!-- End Footer Top -->
		<?php
		}
		?>
		<?php
		if (is_front_page() && is_active_sidebar('header-top-home')) {
		?>
			<div class="header__top header__top-home">
				<div class="container header__container">
					<?php dynamic_sidebar('header-top-home'); ?>
				</div>
			</div><!-- End Footer Top -->
		<?php
		}
		?>
		<div class="header__main position-relative">
			<div class="container header__container">
				<div class="row header__row">
					<div class="flex-auto header__col header__logo">
						<?php get_template_part('template-parts/headers/logo', null, []); ?>
					</div>
					<div class="flex-auto header__col header__nav">
						<?php echo do_shortcode("[yith_woocommerce_ajax_search preset='default']"); ?>
                        <div class="ywcas-popular-searches-wrapper">
                            <div class="ywcas-popular-searches-items d-flex align-content-center justify-content-start">
                                <?php
                                $trending = get_option('yith_wcas_trending_searches_keywords');
                                $trending = explode(', ', $trending);
                                if (!empty($trending)):
                                    echo '<button type="button" class="ywcas-popular-searches-label text-white" data-keyword="">'
                                        . esc_html__('Most searched:', 'twmp-phonghoa') .
                                        '</button>';

                                    $last_index = count($trending) - 1;

                                    foreach ($trending as $index => $item) {
                                        $text = ($index !== $last_index) ? esc_html($item) . ',' : esc_html($item);

                                        echo '<button type="button" class="ywcas-popular-searches-item text-white" data-keyword="' . esc_attr($item) . '">'
                                            . $text .
                                            '</button>';
                                    }
                                endif;
                                ?>
                            </div>
                        </div>
					</div>
					<div class="flex-auto header__col header__actions">
						<div class="header__menu-icons">
							<?php echo do_shortcode('[custom_element id="82"]'); ?>
							<?php
							get_template_part('template-parts/headers/icon-cart', null, [
								'class' => 'header__menu-icons__item header__menu-icons__item--cart header__menu-icons__link js-minicart-trigger'
							]);
							?>
							<div class="th-menu-toggle"><?php echo twmp_get_svg_icon('menu') ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="header__bottom">
            <div class="container">
                <?php get_template_part('template-parts/headers/main-nav', null, []); ?>
            </div>

            <?php /* get_template_part('templates/blocks/category-grid', null, [
                'class' => 'category-grid',
                'enable_container' => true,
                'grid_css_class' => 'col',
                'class_container' => 'header__bottom-container'
            ]) */ ?>
		</div>

	</header>

	<?php do_action('twmp_after_header'); ?>