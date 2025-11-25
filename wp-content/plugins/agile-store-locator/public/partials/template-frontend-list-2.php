<?php


//pol-md-4 pol-lg-3 pol-xl-2
$list_column = [
  'md' => 4,
  'lg' => 3,
  'xl' => 2
];

//pol-md-8 pol-lg-9 pol-xl-10
$map_column = [
  'md' => 8,
  'lg' => 9,
  'xl' => 10
];

$this->overrideColumnConfigs($all_configs, $list_column, $map_column);


list($list_class, $map_class) = $this->createColClasses($list_column, $map_column);


$class = '';

$default_addr = (isset($all_configs['default-addr'])) ? $all_configs['default-addr'] : '';

$container_class    = (isset($all_configs['full_width']) && $all_configs['full_width']) ? 'sl-container-fluid' : 'sl-container';
$geo_btn_class      = ($all_configs['geo_button'] == '1') ? 'asl-geo icon-direction' : 'icon-search';
$geo_btn_text       = ($all_configs['geo_button'] == '1') ? __('Current Location', 'asl_locator') : __('Search', 'asl_locator');
$search_type_class  = ($all_configs['search_type'] == '1') ? 'asl-search-name' : 'asl-search-address';

$ddl_class_grid     = ($all_configs['search_2']) ? 'pol-lg-3 pol-md-6 pol-sm-12' : 'pol-lg-3 pol-md-6 pol-sm-12';

if ($all_configs['tabs_layout'] == '1') {

    $ddl_class_grid    = ' asl-tabs-ddl pol-12 pol-lg-12 pol-md-12 pol-sm-12';
    $class            .= ' sl-dropdown-tabs';
}


$btn_text = ($all_configs['geo_button'] == '1') ? __('Current Location', 'asl_locator') : __('Search Location', 'asl_locator');

?>
<style type="text/css">
    <?php echo esc_attr($css_code); ?>
</style>
<div id="asl-storelocator" class="asl-cont storelocator-main asl-template-list-2 asl-bg-<?php echo esc_attr($all_configs['color_scheme']) ?> asl-text-<?php echo esc_attr($all_configs['font_color_scheme'] . $class); ?>">
    <div class="sl-container ">
        <div id="asl-panel" class="sl-row">
            <div class="asl-overlay" id="map-loading">
                <div class="white"></div>
                <div class="sl-loading">
                    <i class="animate-sl-spin icon-spin3"></i>
                    <?php echo asl_esc_lbl('loading') ?>
                </div>
            </div>
            <div class="pol-12">
                <div class="sl-row">
                    <div class="<?php echo esc_attr($list_class) ?> pb-2">
                        <div class="sl-row asl-filter-sec">
                            <div class="pol-12 pt-4 asl-search-cont">
                                <div class="asl-search-inner">
                                    <div class="sl-row">
                                        <div class="pol-12">
                                            <div class="form-group asl-addr-search">
                                                <label class="asl-cntrl-lbl" for="sl-main-search"><?php echo asl_esc_lbl('search_loc1') ?></label>
                                                <input id="sl-main-search" type="text" class="form-control asl-search-address rounded-left" placeholder="<?php echo asl_esc_lbl('search_loc1') ?>">
                                                <a title="Current Location" class="sl-geo-btn asl-geo">
                                                    <svg width="20px" height="20px" viewBox="0 0 561 561" fill="#333">
                                                        <path d="M280.5,178.5c-56.1,0-102,45.9-102,102c0,56.1,45.9,102,102,102c56.1,0,102-45.9,102-102 C382.5,224.4,336.6,178.5,280.5,178.5z M507.45,255C494.7,147.9,410.55,63.75,306,53.55V0h-51v53.55 C147.9,63.75,63.75,147.9,53.55,255H0v51h53.55C66.3,413.1,150.45,497.25,255,507.45V561h51v-53.55 C413.1,494.7,497.25,410.55,507.45,306H561v-51H507.45z M280.5,459C181.05,459,102,379.95,102,280.5S181.05,102,280.5,102 S459,181.05,459,280.5S379.95,459,280.5,459z"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="asl-sec-filter-cont">
                                    <?php if (isset($all_configs['address_ddl']) && $all_configs['address_ddl'] == '1') : ?>
                                        <div class="sl-row asl-list-ddls asl-ddl-filters"></div>
                                    <?php endif; ?>
                                    <div class="sl-row">
                                        <div class="pol-md-12 sl-dist-cont"></div>
                                        <div class="pol-md-12">
                                            <?php if ($all_configs['show_categories'] == '1') : ?>
                                                <div class="form-group asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl" for="asl-cats-filter"><?php echo asl_esc_lbl('all_categories') ?></label>
                                                    <select multiple="multiple" id="asl-cats-filter" class="asl-select-ctrl form-control rounded-left">
                                                        <option></option>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($all_configs['search_2']) : ?>
                                            <div class="pol-md-12">
                                                <div class="search_filter asl-name-search">
                                                    <div class="asl-filter-cntrl">
                                                        <label for="asl-name-search-field" aria-label="<?php echo asl_esc_lbl('search_name') ?>" title="<?php echo asl_esc_lbl('search_name') ?>" class="asl-cntrl-lbl"><?php echo asl_esc_lbl('search_name') ?></label>
                                                        <div class="sl-search-group">
                                                            <input type="text" id="asl-name-search-field" placeholder="<?php echo asl_esc_lbl('search_name_ph') ?>" class="asl-search-name form-control isp_ignore">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                        <?php
                                        if (isset($filter_ddl))
                                            foreach ($filter_ddl as $key => $label) : ?>
                                            <div class="pol-md-12 asl-ddl-filters d-none">
                                                <div class="asl-filter-cntrl">
                                                    <label class="asl-cntrl-lbl" for="asl-<?php echo esc_attr($key) ?>"><?php echo esc_attr($label); ?></label>
                                                    <div class="sl-dropdown-cont" id="<?php echo esc_attr($key); ?>_filter"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="pol-md-12">
                                            <div class="asl-ddl-filters">
                                                <div class="asl-filter-cntrl">
                                                    <label for="sl-sort-by" class="asl-cntrl-lbl"><?php echo asl_esc_lbl('sort_by') ?></label>
                                                    <div class="sl-dropdown-cont">
                                                        <select id="sl-sort-by" class="sl-sort-by form-control">
                                                            <option value=""><?php echo asl_esc_lbl('distance_title') ?></option>
                                                            <option value="title" selected=""><?php echo asl_esc_lbl('title') ?></option>
                                                            <option value="city"><?php echo asl_esc_lbl('cities') ?></option>
                                                            <option value="state"><?php echo asl_esc_lbl('states') ?></option>
                                                            <option value="cat"><?php echo asl_esc_lbl('categories_tab') ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pol-md-12 pb-4 order-md-last">
                                            <div class="asl-btn d-xl-flex mt-2">
                                                <button class="asl-reset-btn btn-outline-danger w-100 ">
                                                <?php echo asl_esc_lbl('reset') ?>
                                                </button>
                                                <button class="d-none btn-danger w-100 mt-2 mt-xl-0 ml-xl-2 ml-0">
                                                <?php echo asl_esc_lbl('search') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr($map_class) ?>">
                        <div class="asl-list-cont sl-row">
                            <div class="pol-md-12">
                                <div class="sl-row asl-stats mb-3 d-none">
                                    <div class="pol text-left">
                                        <div class="Num_of_store">
                                            <div class="count-result-text"><?php echo asl_esc_lbl('head_title') ?>: <span class="count-result"></span></div>
                                        </div>
                                    </div>
                                    <div class="pol text-right">
                                        <a class="btn btn-asl asl-print-btn"><i class="icon-print"></i> <?php echo asl_esc_lbl('print') ?></a>
                                    </div>
                                </div>
                                <ul id="asl-stores-list" class="sl-list sl-row d-flex flex-wrap">
                                </ul>
                                <div class="sl-row">
                                    <div class="pol-12 mt-5">
                                        <div class="sl-pagination nav mb-2 justify-content-center text-center">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include ASL_PLUGIN_PATH . 'public/partials/_lead_modal.php'; ?>
</div>
<!-- This plugin is developed by "Agile Store Locator for WordPress" https://agilestorelocator.com -->