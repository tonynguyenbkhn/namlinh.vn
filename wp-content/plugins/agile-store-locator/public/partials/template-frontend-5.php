<?php

$list_column = [
'md' => 6,
'lg' => 5,
'xl' => 4,
];

$map_column = [
'md' => 6,
'lg' => 7,
'xl' => 8,
];

$this->overrideColumnConfigs($all_configs, $list_column, $map_column);

list($list_class, $map_class) = $this->createColClasses($list_column, $map_column);

$geo_btn_class      = ($all_configs['geo_button'] == '1')? 'asl-geo icon-direction-outline':'icon-search';
$search_type_class  = ($all_configs['search_type'] == '1')? 'asl-search-name':'asl-search-address';
$panel_order        = (isset($all_configs['map_top']))? $all_configs['map_top']: '2';

$ddl_class_grid = ($all_configs['search_2'])? 'pol-lg-4 pol-md-6 pol-sm-12': 'pol-lg-4 pol-md-6 pol-sm-12';
$tsw_class_grid = ($all_configs['search_2'])? 'pol-lg-2 pol-md-3 pol-sm-12': 'pol-lg-2 pol-md-3 pol-sm-12';
$adv_class_grid = ($all_configs['search_2'])? 'pol-lg-8 pol-md-7': 'pol-lg-8 pol-md-7';

$ddl_class      = '';



$class = (isset($all_configs['css_class']))? ' '.$all_configs['css_class']: '';

if($all_configs['layout'] == '1') {
    $all_configs['advance_filter'] = '0';
    $class .= ' asl-adv-lay1';
}


if($all_configs['display_list'] == '0' || $all_configs['first_load'] == '3' || $all_configs['first_load'] == '4')
$class .= ' map-full';
else if($all_configs['first_load'] == '5') {
$class .= ' sl-search-only';
}

if($all_configs['pickup'] || $all_configs['ship_from'])
$class .= ' sl-pickup-tmpl';

if($all_configs['full_width'])
$class .= ' full-width';

if(isset($all_configs['full_map']))
$class .= ' map-full-width';

if($all_configs['advance_filter'] == '0')
$class .= ' no-asl-filters';

if($all_configs['advance_filter'] == '1' && $all_configs['layout'] == '1')
$class .= ' asl-adv-lay1';


//add Full height
$class .= ' '.$all_configs['full_height'];

$layout_code        = ($all_configs['layout'] == '1'  || $all_configs['layout'] == '2')? '1': '0';
$default_addr       = (isset($all_configs['default-addr']))?$all_configs['default-addr']: '';
$container_class    = (isset($all_configs['full_width']) && $all_configs['full_width'])? 'sl-container-fluid': 'sl-container';

$btn_text = ($all_configs['geo_button'] == '1')?__('Current Location','asl_locator'):__('Search Location','asl_locator');

$all_configs['ddl_max_height'] = 'none';

// Direction Redirect
$all_configs['direction_redirect'] = '2';

?>
<style type="text/css">
    <?php echo esc_attr($css_code); ?>
    @media(max-width:991px){
        #asl-storelocator.asl-cont .asl-panel {order: <?php echo esc_attr($panel_order); ?>;}
    }
</style>
<section id="asl-storelocator" class="asl-cont asl-template-5 sl-category-tabs asl-layout-<?php echo ($all_configs['layout'] != '0')? '1': '0'; ?> asl-bg-<?php echo esc_attr($all_configs['color_scheme'].$class); ?> asl-text-<?php echo esc_attr($all_configs['font_color_scheme']) ?>">
    <div class="asl-wrapper">
        <div class="<?php echo esc_attr($container_class); ?>">
            <!-- GDPR -->
            <?php if($all_configs['gdpr'] == '1'): ?>
              <div class="sl-gdpr-cont">
                  <div class="gdpr-ol"></div>
                  <div class="gdpr-ol-bg">
                    <div class="gdpr-box">
                      <p><?php echo asl_esc_lbl('label_gdpr') ?></p>
                      <a class="btn btn-asl" id="sl-btn-gdpr"><?php echo asl_esc_lbl('load','asl_locator') ?></a>
                    </div>
                  </div>
              </div>
            <?php endif; ?>
            <div class="sl-main-cont">
                <div class="sl-row m-0">
                    <div class="sl-panel-cont <?php echo esc_attr($list_class) ?>">
                        <button class="sl-panel-collapse-btn">
                            <i class="icon-left-open"></i>
                        </button>
                        <?php if($all_configs['advance_filter'] == '1'): ?>
                        <!-- Filter section of the Locator-->
                        <div id="asl-filter-sec-cont" class="Filter_section asl-hide">
                            <div class="sl-row">
                                <div class="pol-sm-12 asl-advance-filters">
                                    <div class="sl-row">
                                        <div class="pol-12">
                                            <div class="d-flex justify-content-between mt-4 mb-3">
                                                <button title="<?php echo asl_esc_lbl('clear_label') ?>" type="button" class="sl-filter-clr-btn"><?php echo asl_esc_lbl('clear_label') ?></button>
                                                <h4 class="sl-filter-lbl mb-0"><?php echo asl_esc_lbl('filters') ?></h4>
                                                <a class="d-flex text-dark" id="asl-filter-sec-close"><i class="icon-cancel-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php if($all_configs['search_2']): ?>
                                        <div class="pol-12 search_filter asl-name-search">
                                            <div class="asl-filter-cntrl mb-lg-2">
                                                 <label for="asl-secondary-search-cntrl" class="asl-cntrl-lbl mb-2 sr-only"><?php echo asl_esc_lbl('search_name') ?></label>
                                                <div class="sl-search-group">
                                                    <input id="asl-secondary-search-cntrl" type="text" tabindex="2" placeholder="<?php echo asl_esc_lbl('search_name_ph') ?>" class="asl-search-name form-control isp_ignore" />
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="pol-12 sl-filter-scrol-panel">
                                            <?php if($all_configs['show_categories']): ?>
                                            <div class="asl-tabs-ddl asl-ddl-filters">
                                                <div class="asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl asl-cntrl-dropdown-cat mb-2"><?php echo asl_esc_lbl('category_title') ?>
                                                        <span class=asl-collapse-arw></span></label>
                                                    <div class="sl-dropdown-cont" id="categories_filter">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if($has_child_categories): ?>
                                            <div class="asl-tabs-ddl asl-ddl-filters asl-ddl-filter-sub-cats d-none">
                                                <div class="asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl asl-cntrl-dropdown-sub-cat" for="asl-sub-categories"><?php echo asl_esc_lbl('sub_cat_label') ?>
                                                        <span class=asl-collapse-arw></span></label>
                                                    <div class="sl-dropdown-cont" id="asl-sub_cats-filter">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if($filter_ddl): ?>
                                            <?php foreach ($filter_ddl as $key => $label):?>
                                            <div class="asl-tabs-ddl asl-ddl-filters asl-ddl-filter-<?php echo esc_attr($key) ?>">
                                                <div class="asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl asl-cntrl-dropdown-cat mb-2" for="asl-<?php echo esc_attr($key) ?>"><?php echo esc_attr($label) ?><span class=asl-collapse-arw></span></label>
                                                    <div class="sl-dropdown-cont" id="<?php echo esc_attr($key) ?>_filter"></div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                            <div class="asl-tabs-ddl asl-ddl-filters hide range_filter">
                                                <div class="rangeFilter asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl asl-ctrl-dropsown-dist"><?php echo asl_esc_lbl('distance_tab') ?></label>
                                                    <input id="asl-radius-slide" type="text" class="span2" />
                                                    <span class="rad-unit"><?php echo asl_esc_lbl('radius') ?>:
                                                        <span id="asl-radius-input"></span>
                                                        <span id="asl-dist-unit"><?php echo asl_esc_lbl('km') ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="asl-ddl-filters Status_filter">
                                                <div class="asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl"><?php echo asl_esc_lbl('status') ?></label>
                                                    <div class="onoffswitch ml-2">
                                                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="asl-open-close" checked>
                                                        <label aria-label="<?php echo asl_esc_lbl('time_switch_label') ?>" title="<?php echo asl_esc_lbl('time_switch_label') ?>" class="onoffswitch-label" for="asl-open-close">
                                                            <span class="onoffswitch-inner"></span>
                                                            <span class="onoffswitch-switch"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- Panel of the Store Locator -->
                        <div id="asl-panel" class="sl-row no-gutters sl-main-row">
                            <div id="asl-list" class="asl-panel sl-main-panel">
                                <div class="asl-overlay" id="map-loading">
                                    <div class="white"></div>
                                    <div class="sl-loading">
                                    <i class="animate-sl-spin icon-spin3"></i>
                                    <?php echo asl_esc_lbl('loading') ?>
                                    </div>
                                </div>
                                <!-- list -->
                                <div class="asl-panel-inner">
                                    <div class="asl-panel-heading">
                                        <h2><?php echo asl_esc_lbl('search_loc') ?></h2>
                                        <div class="asl-panel-txt-desc asl-hide-on-search">
                                            <p>
                                            <?php echo asl_esc_lbl('search_loc_desc') ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="sl-filter-sec d-flex">
                                        <?php if($all_configs['advance_filter'] == '1'): ?>
                                        <div class="asl-filter-div">
                                            <a title="<?php echo asl_esc_lbl('filters') ?>" class="asl-filter-popup-tog"><span><i class="icon-equalizer"></i></span></a>
                                        </div>
                                        <?php endif; ?>
                                        <div class="asl-addr-search">
                                            <input aria-label="<?php echo asl_esc_lbl('enter_loc') ?>"
                                                value="<?php echo esc_attr($default_addr) ?>" id="asl-search-address"
                                                type="text"
                                                class="asl-search-address form-control"
                                                placeholder="<?php echo asl_esc_lbl('enter_loc') ?>" />
                                            <span title="<?php echo __('Search Location','asl_locator') ?>" class="sl-search-btn">
                                            <?php echo asl_esc_lbl('search') ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="sl-main-cont-box">
                                        <div class="sl-list-wrapper">
                                            <ul id="p-statelist"
                                                class="sl-list panel-inner p-0">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Info Panel -->
                        <div id="asl-infobar-panel" class="asl-main-info-panel">
                            <div class="sl-row">
                                <div class="pol-12">
                                    <button type="button" class="sl-info-panel-close-btn">
                                        <i class="icon-left-big"></i>
                                        <?php echo asl_esc_lbl('bck_to_list') ?>
                                    </button>
                                </div>
                                <div class="sl-infobar-section"></div>
                            </div>
                        </div>
                        <!-- Direction Panel -->
                        <div class="directions-cont hide">
                            <div class="agile-modal-header">
                                <button type="button" class="close"><span aria-hidden="true">×</span></button>
                                <h4><?php echo asl_esc_lbl('store_direc') ?></h4>
                            </div>
                            <div class="rendered-directions" id="asl-rendered-dir" style="direction: ltr;"></div>
                        </div>
                        <!-- No Item -->
                        <div class="sl-no-found-section d-none">
                            <div class="sl-no-found">
                                <div class="sl-row">
                                    <div class="pol-12">
                                        <h6><?php echo asl_esc_lbl('no_search_item') ?></h6>
                                        <p><?php echo asl_esc_lbl('no_search_item_desc') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr($map_class) ?> asl-map p-0">
                        <div class="asl-map-inner">
                            <div id="asl-map-canv" class="asl-map-canv"></div>
                            <?php include ASL_PLUGIN_PATH.'public/partials/_agile_modal.php'; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div> 
    </div>
    <?php include ASL_PLUGIN_PATH.'public/partials/_lead_modal.php'; ?>
</section>
<!-- This plugin is developed by "Agile Store Locator for WordPress" https://agilestorelocator.com -->