<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the countries database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/countries
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Countries {


    /**
    * [Get the all countries]
    * @since  4.8.21
    * @return [type]          [description]
    */
  public  static function get_all_countries() {
   
    global $wpdb;

    $ASL_PREFIX   = ASL_PREFIX;
    
    //  Get the results
    $results = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}countries ORDER BY country");

    return $results;
  }

  /**
   * [get_country_id Return the country id]
   * @param  [type] $name [description]
   * @return [type]       [description]
   */
  public static function get_country_id($name) {

    global $wpdb;

    $prefix   = ASL_PREFIX;

    $row      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$prefix}countries WHERE country = %s LIMIT 1", $name ) );
  
    return $row && $row->id ? $row->id : null;
  }


  /**
   * [get_coordinates_via_nominatim A function to get coordinates from Nominatim API]
   * @param string $city The city name
   * @param string $state The state abbreviation
   * @return array|null Returns an array with latitude and longitude if successful, null otherwise
   */
  public static function get_coordinates_via_nominatim($city, $state) {
    
    // Format the query
    $query = urlencode("$city, $state");

    // Construct the URL for the Nominatim API
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=$query";

    // Make a GET request to the API
    $response = file_get_contents($url);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if data is not empty and extract coordinates if available
    if (!empty($data)) {
        $latitude = $data[0]['lat'];
        $longitude = $data[0]['lon'];
        return array('lat' => $latitude, 'lng' => $longitude);
    }
    else {
        return null; // Return null if coordinates are not found
    }
  }


  /**
   * [get_coordinates_via_mapquest A function to get coordinates from MapQuest Geocoding API]
   * @param string $city The city name
   * @param string $state The state abbreviation
   * @param string $api_key Your MapQuest API key
   * @return array|null Returns an array with latitude and longitude if successful, null otherwise
   */
  public static function get_coordinates_via_mapquest($city, $state, $api_key) {
      // Format the query
      $query = urlencode("$city, $state");

      // Construct the URL for the MapQuest Geocoding API
      $url = "https://www.mapquestapi.com/geocoding/v1/address?key=$api_key&location=$query";

      // Make a GET request to the API
      $response = file_get_contents($url);

      // Decode the JSON response
      $data = json_decode($response, true);

      // Check if data is not empty and extract coordinates if available
      if (!empty($data['results'][0]['locations'][0]['latLng'])) {
          $latitude = $data['results'][0]['locations'][0]['latLng']['lat'];
          $longitude = $data['results'][0]['locations'][0]['latLng']['lng'];
          return array('lat' => $latitude, 'lng' => $longitude);
      } else {
          return null; // Return null if coordinates are not found
      }
  }

  /**
   * \AgileStoreLocator\Model\Countries::fill_coordinates_via_api();
   * [fill_coordinates_via_api A function to fill the coordinates by querying Nominatim]
   * @return void
   */
  public static function fill_coordinates_via_api() {
        
      ini_set('memory_limit', '256M');
      ini_set('max_execution_time', 0);

      global $wpdb;

      //Get the Stores
      $stores = $wpdb->get_results("SELECT * FROM ".ASL_PREFIX."stores WHERE (lat = '' OR lng = '') OR (lat = '0.0' OR lng = '0.0') OR (lat IS NULL OR lng IS NULL) OR !(lat BETWEEN -90.10 AND 90.10) OR !(lng BETWEEN -180.10 AND 180.10) OR !(lat REGEXP '^[+-]?[0-9]*([0-9]\\.|[0-9]|\\.[0-9])[0-9]*(e[+-]?[0-9]+)?$') OR !(lng REGEXP '^[+-]?[0-9]*([0-9]\\.|[0-9]|\\.[0-9])[0-9]*(e[+-]?[0-9]+)?$')");

      //  Loop over the stores
      foreach($stores as $store) {

        //  Respect the Free API
        //sleep(0.3);
        $api_key = '';
        

        //$coordinates = ($store->city && $store->state)? self::get_coordinates_via_nominatim($store->city, $store->state): null;
        $coordinates = ($store->city && $store->state)? self::get_coordinates_via_mapquest($store->city, $store->state, $api_key): null;

        if($coordinates) {
            $wpdb->update( ASL_PREFIX.'stores', array('lat' => $coordinates['lat'], 'lng' => $coordinates['lng']), array('id'=> $store->id ));
        }
      }
  }

}
