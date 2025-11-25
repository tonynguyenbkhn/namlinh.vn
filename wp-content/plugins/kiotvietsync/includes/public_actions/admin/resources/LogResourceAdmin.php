<?php
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared


use Kiotviet\Kiotviet\HttpClient;

class LogResourceAdmin
{
    private $wpdb, $HttpClient, $table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->HttpClient = new HttpClient();

        // get table
        $this->table = "{$wpdb->prefix}kiotviet_sync_logs";
    }

    public function create(
        $refId = "",
        $fields = []
    ) {

        return wp_send_json($this->HttpClient->responseSuccess([
            "results" => []
        ]));
    }

    public function update(
        $refId = "",
        $fields = []
    ) {

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
                "SELECT * FROM `$table` ORDER BY id DESC LIMIT %d, %d",
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
            "logs" => $results,
        ]));
    }
    public function delete(
        $refId = "",
        $fields = []
    ) {

        return wp_send_json($this->HttpClient->responseSuccess([
            "results" => []
        ]));
    }
}