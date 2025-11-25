<?php
if ( post_password_required() ) {
    return;
}
global $post;
?>

<div id="comments" class="comments-area <?php echo intval(get_comment_count( $post->ID )['approved']) > 0 ? "has-comment" : "no-comment" ?>">
    <?php if ( get_comment_count( $post->ID )['approved'] >= 1 ) : ?>
        <h3 class="heading-count">
            Comments <span class=""> (<?php echo esc_attr( get_comment_count( $post->ID )['approved'] ); ?>) </span>
        </h3>
    <?php
    endif;
    // You can start editing here -- including this comment!
    if ( have_comments() ) : ?>

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
            <nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
                <h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'twmp-phonghoa' ); ?></h2>
                <div class="nav-links">

                    <div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'twmp-phonghoa' ) ); ?></div>
                    <div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'twmp-phonghoa' ) ); ?></div>

                </div><!-- .nav-links -->
            </nav><!-- #comment-nav-above -->
        <?php endif; // Check for comment navigation. ?>

        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size' => 80,
                'callback' => 'twmp_format_comment'
            ) );
            ?>
        </ol><!-- .comment-list -->

        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
            <nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
                <h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'twmp-phonghoa' ); ?></h2>
                <div class="nav-links">

                    <div class="nav-previous"><?php previous_comments_link( esc_html__( 'Older Comments', 'twmp-phonghoa' ) ); ?></div>
                    <div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments', 'twmp-phonghoa' ) ); ?></div>

                </div><!-- .nav-links -->
            </nav><!-- #comment-nav-below -->
        <?php
        endif; // Check for comment navigation.
    else: 
        echo '<h4 class="form-title no-comment">User Comments</h4><span class="">'.esc_html__( 'No comments posted yet.', 'twmp-phonghoa' ).'</span>';
    endif; // Check for have_comments().


    // If comments are closed and there are comments, let's leave a little note, shall we?
    if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'twmp-phonghoa' ); ?></p>
    <?php endif; ?>
    <?php comment_form(); ?>

</div>
