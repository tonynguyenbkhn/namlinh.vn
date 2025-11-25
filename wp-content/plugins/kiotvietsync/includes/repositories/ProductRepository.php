<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

class ProductRepository
{
    protected $table;
    protected $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'kiotviet_sync_products';
    }

    public function deleteProduct($where)
    {
        return $this->wpdb->delete($this->table, $where);
    }

    public function getCountPostProductWithTitle($retailer, $search)
    {
        $wpdb = $this->wpdb;

        return $wpdb->get_results($wpdb->prepare("
                SELECT COUNT(*) AS total 
                FROM {$wpdb->prefix}posts AS a 
                INNER JOIN {$wpdb->prefix}kiotviet_sync_products AS b 
                    ON a.ID = b.product_id 
                WHERE a.post_status = 'publish' 
                  AND a.post_type = 'product' 
                  AND b.retailer = %s
                  AND a.post_title LIKE %s
            ", $retailer, $search), OBJECT);
    }

    public function getCountPostProduct($retailer)
    {
        $wpdb = $this->wpdb;

        return  $wpdb->get_results($wpdb->prepare("
            SELECT COUNT(*) AS total 
            FROM {$wpdb->prefix}posts AS a 
            INNER JOIN {$wpdb->prefix}kiotviet_sync_products AS b 
                ON a.ID = b.product_id 
            WHERE a.post_status = 'publish' 
              AND a.post_type = 'product' 
              AND b.retailer = %s
        ", $retailer), OBJECT);
    }

    public function getProductWithTitle($retailer, $search, $limit)
    {
        $wpdb = $this->wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}posts` AS a 
                    INNER JOIN `{$wpdb->prefix}kiotviet_sync_products` AS b ON a.ID = b.product_id 
                    WHERE a.post_status = 'publish' AND a.post_type = 'product' 
                    AND b.retailer = %s AND a.post_title LIKE %s ORDER BY b.id DESC LIMIT %d,25",
            $retailer, $search, $limit), ARRAY_A);
    }

    public function getProduct($retailer, $limit)
    {
        $wpdb = $this->wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}posts` AS a 
                    INNER JOIN `{$wpdb->prefix}kiotviet_sync_products` AS b ON a.ID = b.product_id 
                    WHERE a.post_status = 'publish' AND a.post_type = 'product' 
                    AND b.retailer = %s ORDER BY b.id DESC LIMIT %d,25",
            $retailer, $limit), ARRAY_A);
    }

}