<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

require_once plugin_dir_path(dirname(__FILE__)) . '.././vendor/autoload.php';

use Kiotviet\Kiotviet\HttpClient;

class Kiotviet_Sync_Service_Category
{
    private $KiotvietWcCategory;
    private $wpdb;
    private $retailer;
    public function __construct()
    {
        global $wpdb;
        $this->KiotvietWcCategory = new KiotvietWcCategory();
        $this->wpdb = $wpdb;
        $this->retailer = get_option('kiotviet_sync_retailer', "");
        $this->HttpClient = new HttpClient();
    }

    public function delete()
    {
        $table_name = esc_sql($this->wpdb->prefix . 'kiotviet_sync_categories');

        global $wpdb;
        $categorySync = $this->wpdb->get_results($wpdb->prepare(
            "SELECT category_id FROM `$table_name`"
        ));

        foreach ($categorySync as $item) {
            wp_delete_term($item->category_id, 'product_cat');
        }

        $this->wpdb->query($wpdb->prepare("DELETE FROM `$table_name`"));

        wp_send_json($this->HttpClient->responseSuccess(true));
    }

    public function getCategoryIdMap($categoryKvId)
    {
        $categoryMap = [];
        if (!empty($categoryKvId)) {
            global $wpdb;
            $table_name = $this->wpdb->prefix . 'kiotviet_sync_categories';
            $params = array_merge([$this->retailer], $categoryKvId);

            $categorySync = $wpdb->get_results($wpdb->prepare("
                    SELECT * FROM `$table_name` WHERE `retailer` = %s AND `category_kv_id` IN (" . implode(',', array_fill(0, count($categoryKvId), '%s')) . ")",
                ...$params
            ));

            foreach ($categorySync as $item) {
                $categoryMap[$item->category_kv_id] = $item->category_id;
            }
        }

        return $categoryMap;
    }

    public function add()
    {
        $categories = kiotviet_sync_get_request('data', []);
        $categoryKvId = [];

        foreach ($categories as $category) {
            $categoryKvId[] = intval($category['categoryKvId']);
        }

        $categoryMap = $this->getCategoryIdMap($categoryKvId);
        foreach ($categories as $category) {
            if (empty($categoryMap[intval($category["categoryKvId"])])) {
                $category_id = $this->KiotvietWcCategory->add_category($category);
                if (!is_wp_error($category_id)) {
                    $insert = [
                        'category_id' => $category_id,
                        'category_kv_id' => intval($category["categoryKvId"]),
                        'data_raw' => $category["dataRaw"],
                        'retailer' => $this->retailer,
                        'created_at' => kiotviet_sync_get_current_time(),
                    ];
                    $this->insertCategorySync($insert);
                }
            }
        }

        wp_send_json($this->HttpClient->responseSuccess(true));
    }

    public function deleteCategorySync($category_kv_id)
    {
        $delete = [
            "category_kv_id" => $category_kv_id,
            "retailer" => $this->retailer,
        ];
        $this->wpdb->delete($this->wpdb->prefix . "kiotviet_sync_categories", $delete);
    }

    public function insertCategorySync($category)
    {
        global $wpdb;
        $table = esc_sql($this->wpdb->prefix . 'kiotviet_sync_categories');

        $categorySync = $this->wpdb->get_row($wpdb->prepare(
            "SELECT * FROM `$table` WHERE `category_kv_id` = %d AND `retailer` = %s",
            $category['category_kv_id'],
            $this->retailer
        ), ARRAY_A);

        if (!$categorySync) {
            $this->wpdb->insert($this->wpdb->prefix . "kiotviet_sync_categories", $category);
        }
    }

    public function deleteSync()
    {
        $ids = kiotviet_sync_get_request('data', []);
        if ($ids) {
            global $wpdb;
            $tableName = esc_sql($this->wpdb->prefix . 'kiotviet_sync_categories');

            $params = array_merge([$this->retailer], $ids);

            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `$tableName` WHERE `retailer` = %s AND `category_kv_id` IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")",
                    ...$params
                )
            );
        }

        wp_send_json($this->HttpClient->responseSuccess(true));
    }

    public function update()
    {
        $data = kiotviet_sync_get_request('data', []);
        $categoryKvId = [];
        $response = [];
        foreach ($data as $item) {
            $categoryKv = json_decode(html_entity_decode(stripslashes($item['categoryKv'])), true);
            $categoryKvId[] = $categoryKv['categoryId'];
        }

        $categoryMap = $this->getCategoryIdMap($categoryKvId);

        foreach ($data as $item) {
            $categoryKv = json_decode(html_entity_decode(stripslashes($item['categoryKv'])), true);
            if (!empty($categoryMap[$categoryKv['categoryId']])) {
                $item['id'] = $categoryMap[$categoryKv['categoryId']];
                $result = $this->KiotvietWcCategory->edit_category($item);
                if (!is_wp_error($result)) {
                    $response[] = $categoryKv['categoryId'];
                }
            }
        }

        wp_send_json($this->HttpClient->responseSuccess($response));
    }
}