<?php

/**
 * ส่ง JSON response และ exit
 * PHP 5.6 compatible
 */
function json_response($data, $success = true, $http_code = 200)
{
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');

    if ($success) {
        echo json_encode(array('success' => true, 'data' => $data), JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(array('success' => false, 'message' => $data), JSON_UNESCAPED_UNICODE);
    }
    exit;
}

/**
 * ดึง HTTP method ของ request ปัจจุบัน
 */
function get_method()
{
    return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
}

/**
 * ดึง JSON body จาก request
 */
function get_json_body()
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return array();
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

/**
 * Validate ฟิลด์บังคับ
 * คืน null = ผ่าน, string = error message
 */
function validate_required(array $data, array $fields)
{
    foreach ($fields as $field) {
        if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
            return "ฟิลด์ '{$field}' จำเป็นต้องระบุ";
        }
    }
    return null;
}

/**
 * Format จำนวนเงินเป็น string
 */
function format_money($amount)
{
    return '฿' . number_format((float)$amount, 2, '.', ',');
}

/**
 * Sanitize string output (ป้องกัน XSS)
 */
function h($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
