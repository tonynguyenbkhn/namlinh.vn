<?php

$address = urlencode($store_data->address);

if(isset($atts['coords_direction'])) {

    $address = $store_data->lat.','.$store_data->lng;
    $address = urlencode(trim($address));
}

$direction_url = "https://www.google.com/maps/dir/?api=1&destination=".$address;


?>

<section class="asl-cont asl-store-pg" data-config='<?php echo htmlspecialchars(json_encode($all_configs)); ?>'>
    <div class="sl-container">
        <div class="sl-row">
            <div class="pol-lg-6 pol-md-12">
                <div class="sl-row">
                    <?php if($store_data->path): ?>
                    <div class="pol-md-3 pol-sm-3">
                        <div class="img-box">
                            <img src="<?php echo ASL_UPLOAD_URL ?>Logo/<?php echo esc_attr($store_data->path) ?>" alt="<?php echo esc_attr($store_data->title); ?>" class="sl-logo img-thumbnail">
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="<?php if(!$store_data->path):?> pol-md-12 <?php endif; ?>pol-md-9 pol-sm-9">
                       <div class="asl-content-box">
                            <h2 class="sl-store-title mb-2"><?php echo esc_attr($store_data->title); ?></h2>
                            <hr class="bottom-line">
                            <ul class="sl-address list-unstyled">
                                <li class="sl-store-info">
                                    <p><?php echo esc_attr($store_data->street) ?> <br> <?php echo esc_attr($locality) ?></p>
                                </li>
                                <?php if($store_data->phone): ?>
                                <li class="sl-store-info">
                                    <i class="icon-mobile-1"></i>
                                    <p><a href="tel:<?php echo esc_attr($store_data->phone) ?>"><?php echo esc_attr($store_data->phone) ?></a></p>
                                </li>
                                <?php endif; ?>
                                <?php if($store_data->email): ?>
                                <li class="sl-store-info">
                                    <i class="icon-mail"></i>
                                    <p><a href="mailto:<?php echo esc_attr($store_data->email) ?>"><?php echo esc_attr($store_data->email) ?></a></p>
                                </li>
                                <?php endif; ?>
                                <?php if($store_data->open_hours): ?>
                                <li class="sl-store-info">
                                    <i class="icon-clock"></i>
                                    <div class="sl-timings list-unstyled">
                                        <?php echo wp_kses_post($store_data->open_hours) ?>
                                    </div>
                                </li>
                                <?php endif; ?>
                                <?php if($store_brand): ?>
                                <li class="sl-store-info">
                                    <i class="icon-tag"></i>
                                    <p><?php echo esc_attr($store_brand) ?></p>
                                </li>
                                <?php endif; ?>
                                <?php if($store_special): ?>
                                <li class="sl-store-info">
                                    <i class="icon-tag"></i>
                                    <p><?php echo esc_attr($store_special) ?></p>
                                </li>
                                <?php endif; ?>
                            </ul>
                            <?php if($store_categories): ?>
                            <div class="asl-cat-tags">
                              <ul class="cat-tags-list list-unstyled d-flex">
                                <?php foreach ($store_categories as $c): ?>
                                    <li class="cat-tags mb-1"><?php echo esc_attr($c); ?></li>
                                <?php endforeach; ?>
                              </ul>
                            </div>
                            <?php endif; ?>
                            <?php if($store_data->description): ?>
                                <p class="asl-short-decp"><?php echo wp_kses_post($store_data->description) ?></p>
                            <?php endif; ?>
                            <?php if($store_data->description_2): ?>
                                <p class="asl-short-decp"><?php echo wp_kses_post($store_data->description_2) ?></p>
                            <?php endif; ?>
                            <div class="btn-box">
                                <div class="sl-row">
                                    <div class="pol-md-6">
                                        <a href="<?php echo esc_url($direction_url) ?>" target="_blank" class="btn btn-info text-white"><?php echo asl_esc_lbl('direction') ?></a>
                                    </div>
                                    <?php if($store_data->website): ?>
                                    <div class="pol-md-6">
                                        <a target="_blank" href="<?php echo esc_url($store_data->website) ?>" class="btn btn-success"><?php echo asl_esc_lbl('website') ?></a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(isset($store_data->whatsapp) && $store_data->whatsapp): ?>
                                    <div class="pol-md-6">
                                        <a target="_blank" href="https://wa.me/<?php echo esc_attr($store_data->whatsapp) ?>?text=<?php echo esc_attr('Hello', 'asl_locator'); ?>" class="btn whatsapp-btn"><span class="d-flex text-center"><i class="ico-whatsapp mt-1 mr-2"></i> <span><?php echo esc_attr('WhatsApp', 'asl_locator'); ?></span></span></a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                       </div>                
                    </div>
                </div>
            </div>
            <?php if($store_data->map): ?>
            <div class="pol-lg-6 pol-md-12">
                <div class="asl-detail-map"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php 
echo ($google_schema);
?>