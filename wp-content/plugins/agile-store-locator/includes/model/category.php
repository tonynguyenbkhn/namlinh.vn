<?php

namespace AgileStoreLocator\Model;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
*
* To access the categories database table
*
* @package    AgileStoreLocator
* @subpackage AgileStoreLocator/elements/category
* @author     AgileStoreLocator Team <support@agilelogix.com>
*/
class Category {


    /**
    * [Get the all store categories for vc]
    * @since  4.8.21
    * @return [type]          [description]
    */
    public static function get_all_categories( $addon = null) {

        global $wpdb;

        $ASL_PREFIX   = ASL_PREFIX;
        $categories   = [];
        $orde_by      = " `category_name` ;";
        $where_clause = "`lang` = ''";

        //  Get the results
        $results    = $wpdb->get_results("SELECT * FROM {$ASL_PREFIX}categories WHERE {$where_clause} ORDER BY {$orde_by}");

        //  Loop over
        if($addon) {

            foreach ($results as $key => $value) {

                if ($addon === 'asl_vc') {

                    $categories[$value->category_name] =  $value->id;

                } elseif ($addon === 'asl_ele') {

                    $categories[$value->id] = $value->category_name;

                } else {

                     $categories[$value->category_name] =  $value->id;
                }

            }

            return $categories;
        }


        return $results;
    }


    /**
     * [get_category_by_id Return the Category by id]
     * @param  [type] $category_id [description]
     * @return [type]              [description]
     */
    public static function get_category_by_id($category_id) {

    }

    /**
     * [get_categories Get all the categories of a language]
     * @param  [type] $lang          [description]
     * @param  string $category_name [description]
     * @param  [type] $ids           [description]
     * @return [type]                [description]
     */
    public static function get_categories($lang, $category_name = 'category_name', $ids = null, $with_children = true) {

        global $wpdb;
        
        $where_clause = '';


        //  If Ids are provided
        if($ids) {

            //  Filter the numbers
            $ids = explode(',', $ids);
            $ids = array_map( 'absint', $ids );
            $ids = implode(',', $ids);

            $where_clause = " AND id IN ($ids)";
        }

        //  Serve only parents
        if(!$with_children) {
           $where_clause .= " AND parent_id = 0";
        }

        //  Get the results
        $cats    = $wpdb->get_results("SELECT `id`,`category_name` as $category_name, `icon`, `ordr` FROM ".ASL_PREFIX."categories WHERE lang = '$lang' $where_clause ORDER BY category_name ASC");

        //  Loop & filter
        if($cats) {

            foreach($cats as $cat) {

                $cat->$category_name =  esc_attr($cat->$category_name);
            }
        }

        return $cats;
    }

    /**
      * [get_parents Get all the parent categories]
      * @param  string $lang [description]
      * @return [type]       [description]
      */
    public static function get_parent($lang = '', $parent_id = NULL) {


        global $wpdb;

        $ASL_PREFIX   = ASL_PREFIX;
        
        $where_clause = "`lang` = '$lang'";
        $where_clause .= $parent_id ? " AND `id` = $parent_id" : "";
        
        //  Get the results
        $results    = $wpdb->get_row("SELECT * FROM {$ASL_PREFIX}categories WHERE {$where_clause}");
         
        return $results;
    }

    /**
      * [add_category Return category insert_id]
      * @param  [type] $name [description]
      * @param  [type] $lang [description]
      * @return [type]       [description]
      */
      public static function add_category(array $data) {
        global $wpdb;

        $wpdb->insert(
            ASL_PREFIX.'categories',
            $data,
            array('%s','%d','%s','%s','%d')
        );

        return $wpdb->insert_id;
     }




    /**
      * [get_category_by_name Return the category by name]
      * @param  [type] $name [description]
      * @param  [type] $lang [description]
      * @return [type]       [description]
    */
    public static function get_category_by_name($name, $parent_id = 0, $lang = '') {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM ".ASL_PREFIX."categories WHERE lang = '$lang' AND parent_id = $parent_id AND category_name = %s", $name));
    }


    /**
    * [get_app_categories Get all the categories in child/parent hirarachy]
    * @param  [type] $lang          [description]
    * @param  string $filter_clause [description]
    * @return [type]                [description]
    */
    public static function get_app_categories($lang, $filter_clause = '') {

        global $wpdb;

        $all_categories = array();

        // Start building the SQL query
        $sql = "SELECT id, category_name as name, icon, ordr, parent_id FROM " . ASL_PREFIX . "categories";

        // Initialize the array to hold SQL conditions
        $conditions = array();

        // Add language condition if $lang is not null
        if (!is_null($lang)) {
            $conditions[] = $wpdb->prepare("lang = %s", $lang);
        }

        // Add other conditions from $filter_clause if not empty
        if (!empty($filter_clause)) {
            $conditions[] = $filter_clause; // Make sure $filter_clause is safe to include or use $wpdb->prepare if possible
        }

        // Combine conditions into a WHERE clause if not empty
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        // Order the results
        $sql .= " ORDER BY parent_id ASC";


        $results = $wpdb->get_results($sql);
        
        if (!count($results) && strpos($wpdb->last_error, 'parent_id') !== false) {
            \AgileStoreLocator\Activator::add_cat_parent_id();
        }

        //  Has child categories
        $has_childs     = false;

        // Organize categories into a hierarchical structure
        foreach ($results as $_result) {
            
            $category = (object) array(
                'id' => $_result->id,
                'name' => esc_attr($_result->name),
                'icon' => $_result->icon,
                'ordr' => $_result->ordr,
                'children' => array() // Initialize an array to store child categories
            );

            if ($_result->parent_id) {
                
                // If the category has a parent, ensure the parent category is initialized
                if (!isset($all_categories[$_result->parent_id])) {
                    $all_categories[$_result->parent_id] = (object) array('children' => array());
                }
                
                // Add the current category as a child to the parent category
                $all_categories[$_result->parent_id]->children[] = $category;

                $has_childs = true;
            } 
            else {
                // If the category has no parent, add it directly to the main array
                $all_categories[$_result->id] = $category;
            }
        }

        return [$all_categories, $has_childs];
    }


    /**
     * [categories_child_array Return the array of categories and children]
     * @return [type] [description]
     */
    public static function categories_child_array() {

        list($all_categories, $has_child_categories) = self::get_app_categories(null); // null to get all the langs

        // Result array to store the paths
        $result = [];

        // Start building the path from the root
        self::_buildPath($all_categories, $result);

        return $result;
    }


    /**
     * Recursively build the category path.
     *
     * @param array $categories Array of stdClass objects representing categories.
     * @param array &$result Reference to array where results are stored.
     * @param string $path Current path built recursively.
     */
    private static function _buildPath($categories, &$result, $path = '') {
        
        foreach ($categories as $category) {
            $newPath = $path === '' ? $category->name : $path . '>' . $category->name;
            if (empty($category->children)) {
                $result[$category->id] = $newPath;
            } else {
                self::_buildPath($category->children, $result, $newPath);
            }
        }
    }


}
