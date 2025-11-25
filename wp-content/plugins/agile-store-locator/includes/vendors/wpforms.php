<?php

namespace AgileStoreLocator\Vendors;


/**
 *
 * This class defines all the codes of the WPForms Merger with the Agile Store Locator
 *
 * @link       https://agilelogix.com
 * @since      4.7.21
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/includes\vendors
 * @author     Your Name <support@agilelogix.com>
 */
class WPForms {

	
	/**
	 * [__construct description]
	 */
	public function __construct() {

	}

	/**
	 * [wpf_add_store_field Add the hidden field]
	 * @return [type] [description]
	 */
	public function wpf_add_store_field() {

		echo '<div class="wpforms-field asl-wpforms-field-hidden-custom asl_store_id"><input type="hidden" name="asl_store_id" value=""></div>';
	}



	/**
	 * [field_properties_hidden Add the store id to the hidden field]
	 * @param  [type] $properties [description]
	 * @param  [type] $field      [description]
	 * @param  [type] $form_data  [description]
	 * @return [type]             [description]
	 */
	function field_properties_hidden( $properties, $field, $form_data ) {

	    global $wpform_inst;

	    //  Set the value
	    if($field['label'] == 'asl_store_id') {

	        $store = \AgileStoreLocator\Model\Store::get_store_id_via_slug();
	        
	        if($store && $store->id) {

	            $properties[ 'inputs' ][ 'primary' ][ 'attr' ][ 'value' ] = $store->id;
	        }
	    }

	    return $properties;
	}

	/**
	 * [add_store_name_column Add the store name column]
	 */
	public function add_store_name_column( $fields, $entry, $form_data ) {
		
		// have field?
		if($fields && is_array($fields)) {

			//	find our field
			foreach ($fields as $key => $field) {
				
				//	Got our field?
				if ($field['name'] == 'asl_store_id' && !empty($field['value'])) {

					//	Get the Store Details
					$store_details = \AgileStoreLocator\Model\Store::get_store($field['value']);

					//	Replace the ID with the name
					if($store_details) {

						$fields[$key]['value'] = $store_details->title.' (ID:'.$store_details->id.')';
					}
				}
			}	
		}
		
		
		return $fields;
	}




	/**
	 * [change_email_recipient Change the email recipient]
	 * @param  [type] $email     [description]
	 * @param  [type] $fields    [description]
	 * @param  [type] $entry     [description]
	 * @param  [type] $form_data [description]
	 * @return [type]            [description]
	 */
	function change_email_recipient( $email, $fields, $entry, $form_data ) {
		
		// have field?
		if($fields && is_array($fields)) {

			//	find our field
			foreach ($fields as $key => $field) {
				
				//	Got our field?
				if ($field['name'] == 'asl_store_id' && !empty($field['value'])) {

					//	Get the Store Details
					$store_details = \AgileStoreLocator\Model\Store::get_store($field['value']);
					
					//	Replace the ID with the name
					if($store_details && $store_details->email) {

						// Ensure $email['address'] is an array; if not, initialize it as an array
				    if (!is_array($email['address'])) {
				        $email['address'] = array();
				    }

				    // Append the store email to the existing array of email addresses
				    $email['address'][] = $store_details->email;
				    
						//$email['address'] = array($store_details->email);
					}
				}
			}	
		}
		
	
	return $email;
}




}
