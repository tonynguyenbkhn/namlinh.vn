<?php

namespace AgileStoreLocator\Schema;


class Slug {



  /**
   * [slugify Create Slug]
   * @since  4.8.21         [<description>]
   * @param  [type] $store  [description]
   * @return [type]         [description]
   */
  public static function slugify($store, $custom_fields) {
    global $wpdb;

    // All the fields for the slug
    $all_fields  = ($custom_fields && is_array($custom_fields)) ? array_merge($custom_fields, $store) : $store;

    // Slug Attributes
    $slug_fields = \AgileStoreLocator\Helper::get_setting('slug_attr_ddl');

    // Default values
    if(!$slug_fields) {
        $slug_fields = 'title,city';
    }

    // Exploded in array
    $slug_fields = explode(',', $slug_fields);

    $slug_value = [];

    // Make Slug String
    foreach ($slug_fields as $slug_chunk) {
        if(isset($all_fields[$slug_chunk]) && $all_fields[$slug_chunk]) {
            // Use sanitize_title to generate URL-friendly slugs
            $slug_value[] = $all_fields[$slug_chunk];
        }
    }

    // When slug data fields are empty, make it title and city
    if(empty($slug_value)) {
        $slug_value[] = ($all_fields['title']);
        $slug_value[] = ($all_fields['city']);
    }

    $slug_value = implode('-', $slug_value);

    //  Clean double dashes
    $slug_value = preg_replace('/-+/', '-', $slug_value);

    //  Sanitize the URL
    $slug_value = self::_sanitize_text($slug_value);
  
    // Count the slug    
    $count_slug = self::count_slug($slug_value);

    if($count_slug > 0) {
      $slug_value .= '-'.$count_slug;
    }



    return $slug_value;
  }


  /**
   * [_sanitize_text Sanitize the text]
   * @param  [type] $string [description]
   * @return [type]         [description]
   */
  private static function _sanitize_text($string) {

    $string = sanitize_title($string);

    return $string;
  }




  /**
   * [check slug already exist get next count]
   * @since  4.8.21 [<description>]
   * @param  array   $slug [description]
   */
  public static function count_slug($slug='') {

    global $wpdb;

    // Prepare the SQL statement with a placeholder for the slug
    $sql  = $wpdb->prepare("SELECT COUNT(*) AS counter FROM ".ASL_PREFIX."stores WHERE `slug` LIKE %s", $slug . '%');

    // Execute the query and return the results
    $results = $wpdb->get_var($sql);
    
    return $results;
  }





  /**
   * [check slug already exist or not]
   * @since  4.8.21 [<description>]
   * @param  array   $slug [description]
   */
  public static function check_slug($slug='') {

    global $wpdb;  

    $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".ASL_PREFIX."stores WHERE slug =  %s", $slug));
    return $results;

  }


  /**
   * [update canonical for store details page]
   * @since  4.8.33 [<description>]
   * @param  $url [description]
   */
  public static function update_canonical_tag($url) {

		$store_uri = get_query_var('sl-store', false);

		if ($store_uri) {

	 	  $store_details_slug = \AgileStoreLocator\Helper::get_configs('rewrite_slug');
			
      if (!empty($store_details_slug) && !is_null($store_details_slug)) {
				$url = site_url("$store_details_slug/$store_uri/");
			} 
      else {
				$url .= $store_uri . '/';
			}
		}

		return $url;

  }


  /**
   * [update_title_by_store_slug for updating <title> as store title]
   * @since  4.9.8 [<description>]
   * @param  $title [description]
   */
  public static function update_title_by_store_slug($title) {

		$store_uri = get_query_var('sl-store', false);

    if ($store_uri) {

      $store_details = \AgileStoreLocator\Model\Store::get_store_id_via_slug();
      
      if($store_details && isset($store_details->title)) {

        $title['title'] = $store_details->title;
      }
    }


    return $title;
  }


  /**
   * [get_meta_description_by_store_slug for getting meta description as store description]
   * @since  4.11.8 [<description>]
   * @param  $title [description]
   */
  public static function get_meta_description_by_store_slug() {
		
    $store_uri = get_query_var('sl-store', false);

    if ($store_uri) {
      
      $store_details = \AgileStoreLocator\Model\Store::get_store_id_via_slug();
      
      if (isset($store_details->description) && $store_details->description) {
        
        $description = htmlspecialchars($store_details->description);
        $description = str_replace(["\r\n", "\n"], '', $description);
        
        return strip_tags($description);
      }
    }

    return '';
  }

  /**
   * [add_meta_description_by_store_slug for adding meta description as store description]
   * @since  4.9.8 [<description>]
   * @param  $title [description]
   */
  public static function add_meta_description_by_store_slug() {
		
    $description = self::get_meta_description_by_store_slug();

    if ($description) {
      
      \AgileStoreLocator\Helper::add_content_to_head( '<meta name="description" content="' . $description . '">' . PHP_EOL );
    }
  }


  /**
   * [regenerate_all_slugs Reset All the slugs of the stores]
   * @return [type] [description]
   */
  public static function regenerate_all_slugs() {

    global $wpdb;

    $prefix = ASL_PREFIX;

    // Get all stores
    $stores      = \AgileStoreLocator\Model\Store::get_stores();

    //  Count the stores updated
    $counter     = 0;

    foreach ($stores as $key => $store) {

      // Convert object to array
      $store = (array) $store;

      // Generate new slug, todo add the custom field
      $new_slug  = self::slugify($store, null);

      // update all slugs
      $data = [ 'slug' => $new_slug ];
      $where = [ 'id' => $store['id'] ];

      //Count and update all slugs 
      $counter += $wpdb->update( $prefix . 'stores', $data, $where);
    }

    return $counter;
  }


}