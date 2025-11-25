<?php


$list_column = [
  'md' => 5,
  'lg' => 4,
];

$map_column = [
  'md' => 7,
  'lg' => 8,
];

$this->overrideColumnConfigs($all_configs, $list_column, $map_column);


list($list_class, $map_class) = $this->createColClasses($list_column, $map_column);



$geo_btn_class      = ($all_configs['geo_button'] == '1')? 'asl-geo icon-direction-outline':'icon-search';
$search_type_class  = ($all_configs['search_type'] == '1')? 'asl-search-name':'asl-search-address';
$panel_order        = (isset($all_configs['map_top']))? $all_configs['map_top']: '2';

$ddl_class_grid = ($all_configs['search_2'])? 'pol-lg-4 pol-md-6 pol-sm-12': 'pol-lg-4 pol-md-6 pol-sm-12';
$tsw_class_grid = ($all_configs['search_2'])? 'pol-lg-2 pol-md-3 pol-sm-12': 'pol-lg-2 pol-md-3 pol-sm-12';
$adv_class_grid = ($all_configs['search_2'])? 'pol-lg-8 pol-md-7': 'pol-lg-8 pol-md-7';
// $controls = \AgileStoreLocator\Model\Attribute::get_controls();

$ddl_class      = '';


$class = (isset($all_configs['css_class']))? ' '.$all_configs['css_class']: '';

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

if($all_configs['tabs_layout'] == '1') {

  $ddl_class  .= ' asl-tabs-ddl pol-12 pol-lg-12 pol-md-12 pol-sm-12';
  $class      .= ' sl-category-tabs';
}

//add Full height
$class .= ' '.$all_configs['full_height'];

$layout_code        = ($all_configs['layout'] == '1'  || $all_configs['layout'] == '2')? '1': '0';
$default_addr       = (isset($all_configs['default-addr']))?$all_configs['default-addr']: '';
$container_class    = (isset($all_configs['full_width']) && $all_configs['full_width'])? 'sl-container-fluid': 'sl-container';

$btn_text = ($all_configs['geo_button'] == '1')?__('Current Location','asl_locator'):__('Search Location','asl_locator');


?>
<style type="text/css">
  <?php echo esc_attr($css_code); ?>
  #asl-storelocator.asl-cont .sl-main-cont .asl-panel.pol-lg-12 {order: <?php echo esc_attr($panel_order) ?>;}
  #asl-storelocator.asl-cont .sl-main-cont .asl-panel.pol-lg-12 .asl-panel-inner{ position: relative;height: 450px;}
  .asl-cont .onoffswitch .onoffswitch-label .onoffswitch-switch:before {content: "<?php echo asl_esc_lbl('open') ?>" !important;}
  .asl-cont .onoffswitch .onoffswitch-label .onoffswitch-switch:after {content: "<?php echo asl_esc_lbl('all') ?>" !important;}
  @media (max-width: 767px) {
    #asl-storelocator.asl-cont .asl-panel {order: <?php echo esc_attr($panel_order) ?>;}
  }
  .asl-cont.sl-search-only .Filter_section + .sl-row {display: none;}
  .asl-cont .sl-hide-branches,
  .asl-cont .sl-hide-branches:hover {color: #FFF !important; text-decoration: none !important;cursor: pointer;}
</style>
<div id="asl-storelocator" class="storelocator-main asl-cont asl-template-0 asl-layout-<?php echo esc_attr($layout_code); ?> asl-bg-<?php echo esc_attr($all_configs['color_scheme'].$class); ?> asl-text-<?php echo esc_attr($all_configs['font_color_scheme']) ?>">
  <div class="asl-wrapper">
    <div class="<?php echo esc_attr($container_class) ?>">
      <?php if($all_configs['gdpr'] == '1'): ?>
      <div class="sl-gdpr-cont">
          <div class="gdpr-ol"></div>
          <div class="gdpr-ol-bg">
            <div class="gdpr-box">
              <p><?php echo asl_esc_lbl('label_gdpr') ?></p>
              <a class="btn btn-asl" id="sl-btn-gdpr"><?php echo asl_esc_lbl('load') ?></a>
            </div>
          </div>
      </div>
      <?php endif; ?>
      <?php if($all_configs['advance_filter']): ?> 
      <div class="sl-row Filter_section">
          <div class="pol-lg-4 pol-md-5 pol-sm-12 search_filter">
              <label class="mb-2" for="auto-complete-search"><?php echo asl_esc_lbl('search_loc') ?></label>
              <div class="sl-search-group d-flex">
                <input type="text" value="<?php echo esc_attr($default_addr) ?>" data-submit="disable" id="auto-complete-search" placeholder="<?php echo asl_esc_lbl( 'enter_loc') ?>"  class="<?php echo esc_attr($search_type_class) ?> form-control isp_ignore">
                <button aria-label="<?php echo esc_attr($btn_text) ?>" title="<?php echo esc_attr($btn_text) ?>" type="button" class="span-geo"><i class="<?php echo esc_attr($geo_btn_class) ?>"></i></button>
              </div>
          </div>
          <div class="pol-lg-8 pol-md-7 pol-sm-12">
            <div class="sl-row">
              <div class="pol-sm-12 asl-advance-filters hide">
                <div class="sl-row">
                  <?php if($all_configs['search_2']): ?>
                  <div class="pol-lg-4 pol-md-6 asl-name-search">
                    <div class="asl-filter-cntrl mb-lg-2">
                      <label class="asl-cntrl-lbl" for="asl-secondary-search-cntrl"><?php echo asl_esc_lbl('search_name') ?></label>
                      <div class="sl-search-group">
                        <input type="text" placeholder="<?php echo asl_esc_lbl('search_name_ph') ?>" id="asl-secondary-search-cntrl"  class="asl-search-name form-control isp_ignore">
                      </div>
                    </div>
                  </div>
                  <?php endif ?>
                  <?php if($all_configs['show_categories']): ?>
                  <div class="<?php echo esc_attr($ddl_class_grid) ?> <?php echo esc_attr($ddl_class) ?> asl-ddl-filters asl-ddl-filter-cats">
                      <div class="asl-filter-cntrl">
                        <label class="asl-cntrl-lbl" for="asl-categories"><?php echo asl_esc_lbl('category_title') ?></label>
                        <div class="sl-dropdown-cont" id="categories_filter">
                        </div>
                      </div>
                  </div>
                  <?php if($has_child_categories): ?>
                    <div class="<?php echo esc_attr($ddl_class_grid) ?> <?php echo esc_attr($ddl_class) ?> asl-ddl-filters asl-ddl-filter-sub-cats">
                      <div class="asl-filter-cntrl">
                        <label class="asl-cntrl-lbl" for="asl-sub-categories"><?php echo asl_esc_lbl('sub_cat_label') ?></label>
                        <div class="sl-dropdown-cont" id="asl-sub_cats-filter">
                        </div>
                      </div>
                  </div>
                  <?php endif; ?>
                  <?php endif ?>
                  <?php if($filter_ddl): ?>
                  <?php foreach ($filter_ddl as $key => $label):?>
                  <div class="<?php echo esc_attr($ddl_class_grid) ?> <?php echo esc_attr($ddl_class) ?> asl-ddl-filters asl-ddl-filter-<?php echo esc_attr($key) ?>">
                      <div class="asl-filter-cntrl">
                        <label class="asl-cntrl-lbl" for="asl-<?php echo esc_attr($key) ?>"><?php echo esc_attr($label) ?></label>
                        <div class="sl-dropdown-cont" id="<?php echo esc_attr($key) ?>_filter">
                        </div>
                      </div>
                  </div>
                  <?php endforeach; ?>
                  <?php endif; ?>
                  <div class="<?php echo esc_attr($ddl_class_grid) ?> range_filter asl-ddl-filters hide">
                    <div class="rangeFilter asl-filter-cntrl">
                      <label for="asl-radius-slide" class="asl-cntrl-lbl"><?php echo asl_esc_lbl('distance_tab') ?></label>
                      <input id="asl-radius-slide" type="text" class="span2" />
                      <span class="rad-unit"><?php echo asl_esc_lbl('radius') ?>: <span id="asl-radius-input"></span> <span id="asl-dist-unit"><?php echo asl_esc_lbl('km') ?></span></span>
                    </div>
                  </div>
                  <div class="<?php echo esc_attr($tsw_class_grid) ?> Status_filter">
                    <div class="asl-filter-cntrl">
                      <label class="asl-cntrl-lbl"><?php echo asl_esc_lbl('status') ?></label>
                      <div class="onoffswitch">
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
      <div class="sl-row">
        <div class="pol-12">
            <div class="sl-main-cont">
                <div class="sl-row no-gutters sl-main-row">
                    <div id="asl-panel" class="asl-panel <?php echo esc_attr($list_class) ?> asl_locator-panel">
                        <div class="asl-overlay" id="map-loading">
                            <div class="white"></div>
                            <div class="sl-loading">
                              <i class="animate-sl-spin icon-spin3"></i>
                              <?php echo asl_esc_lbl('loading') ?>
                            </div>
                        </div>
                        <?php if(!$all_configs['advance_filter']): ?>
                        <div class="inside search_filter">
                          <label for="auto-complete-search" class="mb-2"><?php echo asl_esc_lbl('search_loc') ?></label>
                          <div class="asl-store-search">
                            <input type="text" value="<?php echo esc_attr($default_addr) ?>" id="auto-complete-search" class="<?php echo esc_attr($search_type_class) ?> form-control" placeholder="<?php echo asl_esc_lbl('enter_loc') ?>">
                            <button type="button" class="span-geo"><i class="asl-geo <?php echo esc_attr($geo_btn_class) ?>" title="<?php echo ($all_configs['geo_button'] == '1')?__('Current Location','asl_locator'):__('Search Location','asl_locator') ?>"></i></button>
                          </div>
                        </div>
                        <?php endif; ?>
                        <!-- list -->
                        <div class="asl-panel-inner">
                            <div class="top-title Num_of_store">
                              <span><span class="sl-head-title"><?php echo asl_esc_lbl('head_title') ?></span>: <span class="count-result">0</span></span>
                              <?php if($all_configs['branches'] == '1'): ?>
                                <a title="<?php echo asl_esc_lbl('bck_to_list') ?>" class="sl-hide-branches d-none"><i class="icon-back mr-1"></i><?php echo asl_esc_lbl('bck_to_list') ?></a>
                              <?php elseif (isset($all_configs['print_btn']) && $all_configs['print_btn'] != '0'): ?>
                                <a class="asl-print-btn" aria-label="<?php echo asl_esc_lbl('print') ?>"><span><?php echo asl_esc_lbl('print') ?></span><span class="asl-print"></span></a>
                              <?php endif; ?>
                            </div>
                            <div class="sl-main-cont-box">
                              <div id="asl-list" class="sl-list-wrapper">
                                <ul id="p-statelist" class="sl-list">
                                </ul>
                              </div>
                            </div>
                        </div>

                        <div class="directions-cont hide">
                            <div class="agile-modal-header">
                                <button type="button" class="close"><span aria-hidden="true">×</span></button>
                                <h4><?php echo asl_esc_lbl('store_direc') ?></h4>
                            </div>
                            <div class="rendered-directions" id="asl-rendered-dir" style="direction: ltr;"></div>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr($map_class) ?> asl-map">
                        <div class="map-image">
                            <div id="asl-map-canv" class="asl-map-canv"></div>
                            <?php include ASL_PLUGIN_PATH.'public/partials/_agile_modal.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
    <?php include ASL_PLUGIN_PATH.'public/partials/_lead_modal.php'; ?>
  </div>
</div>
<!-- This plugin is developed by "Agile Store Locator for WordPress" https://agilestorelocator.com -->