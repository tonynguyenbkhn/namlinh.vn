<?php



////////////////////
// Contact Form 7 //
////////////////////
if(isset($configs['cf7_hook']) && $configs['cf7_hook'] == '1') {
  
  try {
  	
	  //  Initialize the class
	  $cf7_class = new \AgileStoreLocator\Vendors\CF7();

	  //  Add the hook
	  $cf7_class->before_mail_hook();
  } 
  catch (\Exception $e) {
  	
  }
}


//////////////////////////
//  WPForms does exist! //
//////////////////////////
if(!defined('ASL_DISABLE_WPFORMS') && class_exists( 'WPForms' )) {

	try {
		
	  $wpforms_class = new \AgileStoreLocator\Vendors\WPForms();

	  //  Hook add our hidden field
	  /*
	  if(\AgileStoreLocator\Helper::get_configs('wpfrm_asl_field') == '1') {

	    add_action( 'wpforms_display_submit_before', [$wpforms_class, 'wpf_add_store_field'], 30 );
	  }
	  */

	  //  Add the store details to the form
	  add_filter( 'wpforms_entry_save_data', [$wpforms_class, 'add_store_name_column'], 10, 3 );

	  //  Process to change the recipient
	  if(\AgileStoreLocator\Helper::get_configs('wpfrm_store_notify')) {

	    //  Add the hidden field via slug
	    add_filter( 'wpforms_field_properties_hidden', [$wpforms_class, 'field_properties_hidden'], 10, 3 );

	    //  Chagne the email recipcient
	    add_filter( 'wpforms_entry_email_atts', [$wpforms_class, 'change_email_recipient'], 10, 4 );
	  }

  }
	catch (\Exception $e) {}
}


/////////////////////////////
//  When Yoast is enabled? //
/////////////////////////////
if(!defined('ASL_DISABLE_YOAST') &&  defined('WPSEO_VERSION')) {

	try {
		
	  //  Initialize the class
	  $yoast_class = new \AgileStoreLocator\Vendors\Yoast();

	  //  Add the hook
	  $yoast_class->register_hook();

  }
	catch (\Exception $e) {}
}

//$google_matrix_class = new \AgileStoreLocator\Vendors\Matrix();
  
/**
 * Code for the MathRank
 */
if(!defined('ASL_DISABLE_RANKMATH') && class_exists('RankMath\Sitemap\Sitemap') && interface_exists('RankMath\Sitemap\Providers\Provider')) {

	try {
		
	
  require_once ASL_PLUGIN_PATH.'includes/vendors/rank-math.php';

  add_filter( 'rank_math/sitemap/enable_caching', '__return_false');
  
  add_filter('rank_math/sitemap/providers', function( $external_providers ) {
    $external_providers['custom'] = new \RankMath\Sitemap\Providers\ASLRankMath();
    return $external_providers;
  });

  add_filter( 'rank_math/frontend/title',  [\RankMath\Sitemap\Providers\ASLRankMath::class,'update_page_title_by_store_slug'] );

  add_filter( 'rank_math/frontend/canonical', [\AgileStoreLocator\Schema\Slug::class, 'update_canonical_tag']);

  }
  catch (\Exception $e) { }
}


/*
SEOPRESS
*/
if(!defined('ASL_DISABLE_SEOPRESS') && defined('SEOPRESS_VERSION')) {

	try {
		
	  $seopress_class = new \AgileStoreLocator\Vendors\SeoPress();
  	}
	catch (\Exception $e) { }
}



/**
 * Code for SEOFramework
 */
if(!defined('ASL_DISABLE_SEOFRAMEWORK') && defined('THE_SEO_FRAMEWORK_VERSION')) {

	try {
			
	  add_filter('the_seo_framework_meta_render_data', function($data) {

      // Check and update the canonical URL
      if (isset($data['canonical']['attributes']['href'])) {
          $data['canonical']['attributes']['href'] = \AgileStoreLocator\Schema\Slug::update_canonical_tag($data['canonical']['attributes']['href']);
      }

      // Optionally, update the OG URL as well, if needed
      if (isset($data['og:url']['attributes']['content'])) {
          $data['og:url']['attributes']['content'] = \AgileStoreLocator\Schema\Slug::update_canonical_tag($data['og:url']['attributes']['content']);
      }

      return $data;
      
	  }, 10, 1);

	  add_filter('the_seo_framework_title_from_custom_field', function($title) {


	    $store_uri = get_query_var('sl-store', false);

	    if ($store_uri) {

	      $store_details = \AgileStoreLocator\Model\Store::get_store_id_via_slug();
	      
	      if($store_details) {

	        $title = $title.$store_details->title;
	      }
	    }

	    return $title;

	  });

  }
	catch (\Exception $e) { }
}