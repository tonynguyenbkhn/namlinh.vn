<div class="sl-list-item">
    <div class="sl-item-ctn-box">

        <?php if ($store->path) : ?>
            <div class="asl-logo-box">
                <img src="<?php echo esc_attr($store->path); ?>" alt="<?php echo esc_attr($store->title); ?>" class="img-fluid">
            </div>
        <?php endif; ?>
        <div class="asl-item-box">
            <?php if ( $store->title ) : ?>
                <<?php echo esc_attr($heading_tag); ?> class="asl-card-title">
                    <?php if ( $store->url ) : ?>
                        <a target="<?php echo esc_attr($anchor_target); ?>" href="<?php echo esc_url($store->url); ?>"><?php echo esc_attr($store->title); ?></a>
                    <?php else : ?>
                        <?php echo esc_attr($store->title); ?>
                    <?php endif; ?>
                </<?php echo esc_attr($heading_tag); ?>>
            <?php endif; ?>
            <?php if ( $store->address) : ?>
                <span class="asl-addr"><?php echo esc_attr($store->address); ?></span>
            <?php endif; ?>

            <?php if ($store->rating) : ?>
                <div class="wpmb-rating">
                <span class="wpmb-stars">
                    <div class="wpmb-stars-out icon-star">
                    <div style="width:<?php echo esc_attr($store->rating * 20); ?>%" class="wpmb-stars-in icon-star"></div>
                    </div>
                </span>
                <span class="wpmb-rating-text"><?php echo esc_attr($store->rating); ?> <?php echo esc_attr($store->rating); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($store->phone) : ?>
                <a href="" class="asl-prod-link"><?php echo esc_attr($store->phone); ?></a>
            <?php endif; ?>
            <?php if ($store->email) : ?>
                <a href="mailto:<?php echo esc_attr($store->email); ?>" class="asl-prod-link"><?php echo esc_attr($store->email); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>