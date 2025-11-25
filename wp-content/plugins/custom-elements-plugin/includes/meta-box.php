<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add meta box for hook selection
add_action( 'add_meta_boxes', 'custom_elements_add_meta_box' );

function custom_elements_add_meta_box() {
    add_meta_box(
        'custom_elements_hook',
        'Hook Settings',
        'custom_elements_meta_box_callback',
        'custom_element',
        'side'
    );
}

function custom_elements_meta_box_callback( $post ) {
    $hook = get_post_meta( $post->ID, '_custom_elements_hook', true );
    ?>
    <label for="custom_elements_hook">Hook Name:</label>
    <input type="text" id="custom_elements_hook" name="custom_elements_hook" value="<?php echo esc_attr( $hook ); ?>" style="width: 100%;" />
    <p>Enter the name of the WordPress hook (e.g., <code>wp_footer</code>, <code>generate_after_footer</code>).</p>
    <?php
}

// Save hook data
add_action( 'save_post', 'custom_elements_save_meta_box_data' );

function custom_elements_save_meta_box_data( $post_id ) {
    if ( array_key_exists( 'custom_elements_hook', $_POST ) ) {
        update_post_meta( $post_id, '_custom_elements_hook', sanitize_text_field( $_POST['custom_elements_hook'] ) );
    }
}
