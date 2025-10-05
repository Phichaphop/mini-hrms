<?php
// /config/helpers.php
// Global Helper Functions

/**
 * Generate URL with BASE_URL prefix
 * @param string $path
 * @return string
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Redirect to URL
 * @param string $path
 */
function redirect($path) {
    header('Location: ' . url($path));
    exit();
}

/**
 * Get asset URL
 * @param string $path
 * @return string
 */
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Check if current page matches
 * @param string $page
 * @return bool
 */
function is_current_page($page) {
    return basename($_SERVER['PHP_SELF']) === $page;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function format_date($date, $format = 'M d, Y') {
    if (empty($date)) return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime
 * @return string
 */
function format_datetime($datetime) {
    if (empty($datetime)) return 'N/A';
    return date('M d, Y H:i', strtotime($datetime));
}

/**
 * Get status badge class
 * @param string $status
 * @return string
 */
function status_badge_class($status) {
    $classes = [
        'New' => 'bg-yellow-100 text-yellow-800',
        'In Progress' => 'bg-blue-100 text-blue-800',
        'Complete' => 'bg-green-100 text-green-800',
        'Cancelled' => 'bg-red-100 text-red-800',
        'Active' => 'bg-green-100 text-green-800',
        'Inactive' => 'bg-gray-100 text-gray-800',
        'Terminated' => 'bg-red-100 text-red-800'
    ];
    
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Sanitize output
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if value is empty or null
 * @param mixed $value
 * @param string $default
 * @return mixed
 */
function nullable($value, $default = 'N/A') {
    return empty($value) ? $default : $value;
}
?>