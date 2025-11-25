<div class="sl-list-item">
    <div class="sl-item-ctn-box">
        <?php if ( $store->path ) : ?>
            <div class="asl-logo-box">
                <img src="<?php echo esc_attr($store->path); ?>" alt="<?php echo esc_attr($store->title); ?>" class="img-fluid">
            </div>
        <?php endif; ?>

        <?php if ( $store->title_phone_email ) : ?>
            <div class="asl-item-box">
                <?php if ( $store->title ) : ?>
                    <<?php echo esc_attr($heading_tag); ?> class="asl-card-title">
                        <?php if ( $store->url ) : ?>
                            <a class="asl-card-title" target="<?php echo esc_attr($anchor_target); ?>" href="<?php echo esc_url($store->url); ?>"><?php echo esc_attr($store->title); ?></a>
                        <?php else : ?>
                            <?php echo esc_attr($store->title); ?>
                        <?php endif; ?>
                    </<?php echo esc_attr($heading_tag); ?>>
                <?php endif; ?>

                <?php if ( $store->address ) echo '<span class="asl-addr">' . esc_attr($store->address) . '</span>'; ?>

            </div>
        <?php endif; ?>
    </div>
    <div class="addr-loc addr-loc-style1">
        <ul>
            <?php if ( $store->phone ) : ?>
                <li>
                    <i class="icon-mobile-1"></i>
                    <a><?php echo esc_attr($store->phone); ?></a>
                </li>
            <?php endif; ?>

            <?php if ($store->rating) : ?>
                <div class="wpmb-rating">
                <span class="wpmb-stars">
                    <div class="wpmb-stars-out icon-star">
                    <div style="width:<?php echo ($store->rating * 20); ?>%" class="wpmb-stars-in icon-star"></div>
                    </div>
                </span>
                <span class="wpmb-rating-text"><?php echo esc_attr($store->rating); ?> <?php echo esc_attr($store->rating); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ( $store->email ) : ?>
                <li>
                    <i class="icon-mail"></i>
                    <a><?php echo esc_attr($store->email); ?></a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php if ( $store->direction || $store->website ) : ?>
        <div class="sl-item-btn">
            <ul>
                <?php
                    if ( $store->direction ) echo '<li><a href="' . esc_url($store->direction) . '" target="<?php echo esc_attr($anchor_target); ?>">' .esc_attr__('Direction','asl_locator') . '</a></li>';
                    if ( $store->website ) echo '<li><a href="' . esc_url($store->website) . '" class="btn-solid" target="<?php echo esc_attr($anchor_target); ?>">' .esc_attr__('Website','asl_locator') . '</a></li>';
                ?>
            </ul>
        </div>
    <?php endif; ?>

</div>