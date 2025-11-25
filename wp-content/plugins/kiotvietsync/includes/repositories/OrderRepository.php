<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

class OrderRepository
{
    protected $wpdb;
    protected $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $wpdb->prefix . "kiotviet_sync_orders";
    }

    public function getOrderByKvId($kvOrderId)
    {
        $wpdb = $this->wpdb;

        $tableName = esc_sql($this->tableName);

        return $this->wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `$tableName` WHERE order_kv_id = %d",
                $kvOrderId
            ),
            ARRAY_A
        );
    }

    public function getOrdersByOrderId($kvOrderId)
    {
        $wpdb = $this->wpdb;

        $tableName = esc_sql($this->tableName);

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `$tableName` WHERE order_id IN (". implode(',', array_fill(0, count($kvOrderId), '%d')) . ")",
            ...$kvOrderId
        ));
    }
}