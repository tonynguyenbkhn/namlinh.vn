<?php

namespace AgileStoreLocator\Form;


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * The Custom Field Classes, used in the Form builder
 *
 *
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/Form
 * @author     AgileLogix <support@agilelogix.com>
 */
class CustomField extends Field {
    
    public function __construct($data, $value = '') {

        $require = (isset($data['require']) && $data['require'])? true: false;
        parent::__construct($data['label'], $data['name'], $data['type'], $value, $require);
        $this->options = $this->parseOptions(isset($data['options'])? $data['options']: []);
    }

    private function parseOptions($options) {

        if ($this->type === 'dropdown' || $this->type === 'radio') {
            return explode(',', $options);
        }
        return [];
    }
}