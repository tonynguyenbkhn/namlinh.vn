<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

class LogRepository
{
    protected $wpdb;
    protected $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = $this->wpdb->prefix . "kiotviet_sync_logs";
    }

    public function insert($data, $format)
    {
        $this->wpdb->insert($this->tableName, $data, $format);
    }

    public function getLogsByPage($start, $per_page)
    {
        $wpdb = $this->wpdb;

        $tableName = esc_sql($this->tableName);

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `$tableName` ORDER BY id DESC LIMIT %d, %d",
            $start,
            $per_page
        ), ARRAY_A);
    }

    public function getCountLogs()
    {
        $wpdb = $this->wpdb;

        $tableName = esc_sql($this->tableName);

        return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) as cnt FROM `$tableName`"));
    }
}