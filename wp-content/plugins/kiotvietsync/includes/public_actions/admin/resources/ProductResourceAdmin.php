<?php
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

use Kiotviet\Kiotviet\HttpClient;

class ProductResourceAdmin
{
    private $wpdb, $HttpClient, $table, $productService;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->HttpClient = new HttpClient();

        // get table
        $this->table = "{$wpdb->prefix}kiotviet_sync_products";

        $this->productService = new Kiotviet_Sync_Service_Product;
    }


    public function create(
        $refId = "",
        $fields = []
    ) {

        if(!isset($fields['products'])) {
            return wp_send_json($this->HttpClient->responseSuccess([
                "error" => true,
                "message" => "not found fields products"
            ]));
        }

        try {

            $this->productService->addProductWC($fields['products'], function ($result) {
                return $result;
            });

        } catch (\Exception $e) {
            return wp_send_json($this->HttpClient->responseSuccess([
                "error" => true,
                "message" => $e->getMessage()
            ]));
        }


        return wp_send_json($this->HttpClient->responseSuccess([
            "message" => "OK"
        ]));
    }

    public function update(
        $refId = "",
        $fields = []
    ) {

        try {
            global $wpdb;
            // product sync
            $table = esc_sql($this->table);

            $productSync = $this->wpdb->get_row(
                $wpdb->prepare(
                    "SELECT product_id FROM `$table` WHERE product_kv_id = %d",
                    $refId
                ),
                ARRAY_A
            );

            // delete from wordpress
            $product = wc_get_product($productSync['product_id']);
            if($product) {

                foreach($fields as $key => $val) {
                    $product->{"set_{$key}"}($val);
                }

                // save
                $product->save();
            }
        } catch (\Exception $e) {
            return  wp_send_json($this->HttpClient->responseError([
                "status" => 500
            ], $e->getMessage()));
        }


        return wp_send_json($this->HttpClient->responseSuccess([
            "results" => []
        ]));
    }

    public function read(
        $refId = "",
        $fields = []
    ) {

        $from = isset($fields['from'])? $fields['from']: 0;
        $limit = isset($fields['limit'])? $fields['limit']: 10;

        global $wpdb;
        // get result
        $table = esc_sql($this->table);
        $results = $this->wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `$table` LIMIT %d, %d",
                $from,
                $limit
            ),
            ARRAY_A
        );

        // get total
        $total = $this->wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) as total FROM `$table`"
            )
        );

        return wp_send_json($this->HttpClient->responseSuccess([
            "total" => $total,
            "wp_products" => array_map(function ($product) {
                return $product->get_data();
            }, wc_get_products([])) ,
            "products" => $results,

        ]));
    }

    public function delete(
        $refId = "",
        $fields = []
    ) {

        // delete from db
        try {
            global $wpdb;
            $table = esc_sql($this->table);
            // product sync
            $productSync = $this->wpdb->get_row(
                $wpdb->prepare(
                    "SELECT product_id FROM `$table` WHERE product_kv_id = %d",
                    $refId
                ),
                ARRAY_A
            );

            // delete from wordpress
            $product = wc_get_product($productSync['product_id']);
            if($product) {
                $product->delete();
            }

            global $wpdb;
            // delete from db
            $this->wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `$table` WHERE `product_kv_id` = %d",
                    $refId
                )
            );
        } catch (\Exception $e) {
            return  wp_send_json($this->HttpClient->responseError([
                "status" => 500
            ], $e->getMessage()));
        }

        return wp_send_json($this->HttpClient->responseSuccess([
            "results" => []
        ]));
    }

}