<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the config database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/config
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Config {


  /**
   * [export_config Export All config values]
   * @param  [type] $type [description]
   * @return [type]       [description]
   */
  public static function export_config() {

    global $wpdb;

    $prefix       = ASL_PREFIX;

    //  Fetch the configs
    $configs     = $wpdb->get_results("SELECT `key`, `value`, `type` FROM {$prefix}configs WHERE `type` != 'sync'");

    //  Config to return
    $all_configs = array();
    
    //  Loop over the config
    foreach($configs as $_config)
      $all_configs[$_config->key] = [$_config->value, $_config->type];


    //  Fetch the settings
    $settings  = $wpdb->get_results("SELECT `name`, `content`, `type` FROM {$prefix}settings");


    return ['configs' => $all_configs, 'settings' => $settings];
  }

  /**
   * [import_configuration Import all the configuration of the Configs including labels]
   * @param  [type] $json_text [String]
   * @return [type]            [description]
   */
  public static function import_configuration($json_text) {

    global $wpdb;

    $prefix       = ASL_PREFIX;


    $insert_count = 0;

    //  Decode the JSON
    $json_array  = json_decode($json_text, true); 

    if(isset($json_array) && is_array($json_array)) {

      //////////////////
      //  Config JSON //
      //////////////////
      $config_json = $json_array['configs'];

      //  Make sure the config is correct
      if(is_array($config_json) && isset($config_json['default_lat']) && isset($config_json['default_lng'])) {

        //  Truncate existing configs
        $wpdb->query("TRUNCATE TABLE `{$prefix}configs`");

        //  Loop over the config JSON
        foreach ($config_json as $config_key => $config) {
          
          $insert_count += $wpdb->insert(ASL_PREFIX."configs", array('key' => $config_key, 'value' => $config[0], 'type' => $config[1]));
        }
      }


      //////////////
      // Settings //
      //////////////
      $all_settings = $json_array['settings'];

      if(is_array($all_settings)) {

        foreach ($all_settings as $_setting) {
          \AgileStoreLocator\Helper::set_setting($_setting['content'], $_setting['type'], $_setting['name']);
        }

      }
    }

    return $insert_count;
  }

}
