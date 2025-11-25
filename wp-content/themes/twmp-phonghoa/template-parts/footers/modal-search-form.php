<?php

$data = wp_parse_args($args, [
    'class' => '',
    'id' => '',
    'attributes' => '',
    'close_button_class' => ''
]);

$_class = 'modal';
$_class .= !empty( $data['class'] ) ? esc_attr(' ' . $data['class'] ) : '';

$_attributes = sprintf('id="%s" role="dialog"', $data['id']);
$_attributes .= !empty($data['attributes']) ? ' ' . $data['attributes'] : '';

$_close_button_class = !empty($data['close_button_class']) ? $data['close_button_class'] : 'js-close-button';

?>

<div class="<?php echo $_class; ?>" <?php echo $_attributes; ?>>
    <div class="modal__wrapper">
        <div class="modal__header">
            <span class="modal__title"><?php esc_html_e('Type to search', 'twmp-phonghoa'); ?></span>
            <button class="modal__close-button" data-close-modal="modal-search-form" aria-label="<?php echo esc_attr__('Close a search form modal', 'twmp-phonghoa'); ?>">
                <?php echo twmp_get_svg_icon('close'); ?>
            </button>
        </div>
        <div class="modal__content js-content">
            <?php
            get_search_form(array(
                'id' => 'modal-search-form',
                'echo' => true
            ));
            ?>
        </div>
        <button class="modal__close-button <?php echo $_close_button_class; ?>" data-close-modal="modal-search-form" aria-label="<?php _e('Close a modal', 'twmp-phonghoa'); ?>">
            <?php echo twmp_get_svg_icon('close'); ?>
        </button>
    </div>
</div>
