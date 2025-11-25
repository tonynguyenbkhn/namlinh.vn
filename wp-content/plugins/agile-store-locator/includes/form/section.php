<?php

namespace AgileStoreLocator\Form;


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * The Section Class, used in the Form builder
 *
 *
 * @package    AgileStoreLocator
 * @subpackage AgileStoreLocator/Form
 * @author     AgileLogix <support@agilelogix.com>
 */
class Section {
    
    protected $title;
    protected $fields = [];

    public function __construct($title) {
        $this->title = $title;
    }

    public function addField(Field $field) {
        $this->fields[] = $field;
    }

    public function render() {
        $sectionHTML = '<div class="pol-md-12">
                            <h3 class="sl-sub-title">' . $this->title . '</h3>
                        </div>';

        foreach ($this->fields as $field) {
            $sectionHTML .= $field->render();
        }

        return $sectionHTML;
    }
}