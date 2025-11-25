<?php
require_once plugin_dir_path(__FILE__) . '/../includes/repositories/LogRepository.php';

if (!function_exists('kv_sync_log')) {
    function kv_sync_log($from, $to, $body, $request = "", $type = "", $refer_id = 0)
    {
        $created_at = current_time('mysql');

        $data = [
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'data' => $request,
            'created_at' => $created_at,
            'type' => $type,
            'refer_id' => $refer_id
        ];
        $format = array('%s', '%s', '%s', '%s', '%s', '%d', '%d');

        $logRepository = new LogRepository();

        $logRepository->insert($data, $format);
    }
}