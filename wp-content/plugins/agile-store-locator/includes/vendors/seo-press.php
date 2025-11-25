<?php

namespace AgileStoreLocator\Vendors;

defined('ABSPATH') || exit;

/**
 * Implement the SeoPress class
 */
class SeoPress {

    public function __construct() {
        add_filter('seopress_sitemaps_cpt', [$this, 'add_custom_post_type']);
        add_filter('seopress_titles_title', [$this, 'filter_titles_title']);
        add_filter('seopress_titles_canonical', [$this, 'filter_titles_canonical']);
        add_filter('seopress_titles_desc', [$this, 'filter_description']);
    }

    public function add_custom_post_type($post_types) {
        if (defined('ASL_REGISTER_TYPE')) {
            $post_types['asl_stores'] = (object)[
                'name' => 'asl_stores',
                'labels' => (object)[
                    'name' => 'asl_stores'
                ],
            ];
        }
        return $post_types;
    }

    /**
     * Filter the description
     *
     * @param string $description
     * @return string
     */
    public function filter_description($description) {
        
        $store_uri = get_query_var('sl-store', false);

        if ($store_uri) {
        
            $description = \AgileStoreLocator\Schema\Slug::get_meta_description_by_store_slug();
        }

        return $description;
    }
    

    /**
     * Filter the title
     *
     * @param string $title
     * @return string
     */
    public function filter_titles_title($title) {
        
        $store_uri = get_query_var('sl-store', false);

        if ($store_uri) {

            $store_details = \AgileStoreLocator\Model\Store::get_store_id_via_slug();
            
            if($store_details && isset($store_details->title)) {

                $title = $store_details->title;
            }
        }

        return $title;
    }

    /**
     * Filter the canonical URL
     *
     * @param string $html
     * @return string
     */
    public function filter_titles_canonical($html) {
       
	    $canonical_url = \AgileStoreLocator\Schema\Slug::update_canonical_tag('');
        
        if($canonical_url) {

            $html = '<link rel="canonical" href="'.htmlspecialchars(urldecode($canonical_url)).'" />'; 
        }
	
	    return $html;
    }
}