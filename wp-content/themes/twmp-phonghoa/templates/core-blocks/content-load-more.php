<?php

$data = wp_parse_args(
	$args,
	array(
		'class'      => '',
		'content'    => '',
		'open_text'  => _x( 'View more', 'content load more button', 'twmp-phonghoa' ),
		'close_text' => _x( 'Collapse', 'content load more button', 'twmp-phonghoa' ),
		'button_class' => '',
		'svg_icon'   => '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M12 17.414 3.293 8.707l1.414-1.414L12 14.586l7.293-7.293 1.414 1.414L12 17.414z"/></svg>',
		'height'     => 200,
	)
);

$_class  = 'content-load-more';
$_class .= ! empty( $data['class'] ) ? esc_attr( ' ' . $data['class'] ) : '';

$_button_class = 'btn btn-link text-decoration-none content-load-more__button js-trigger';
$_button_class .= !empty( $data['button_class'] ) ? esc_attr(' ' . $data['button_class']) : ' fw-bold uppercase d-inline-flex align-items-center';

?>
<div class="<?php echo esc_attr( $_class ); ?>"
	data-block="content-load-more"
	data-max-height="<?php echo esc_attr( $data['height'] ); ?>"
	data-open-text="<?php echo esc_attr( $data['open_text'] ); ?>"
	data-close-text="<?php echo esc_attr( $data['close_text'] ); ?>"
>
	<div class="overflow-hidden content-load-more__main js-wrapper-content" style="overflow: hidden; max-height: <?php echo esc_attr( $data['height'] . 'px' ); ?>">
		<div class="content-load-more__content js-content">
			<?php echo wpautop( do_shortcode( $data['content'] ) ); ?>
		</div>
	</div>
	<div class="content-load-more__footer">
		<button class="<?php echo esc_attr( $_button_class ); ?>">
			<span class="text pe-none js-trigger-text"><?php echo esc_html( $data['open_text'] ); ?></span>
			<?php if ( !empty($data['svg_icon']) ) : ?>
				<span class="icon text-primary pe-none" aria-hidden="true"><?php echo $data['svg_icon']; ?></span>
			<?php endif; ?>
		</button>
	</div>
</div>
