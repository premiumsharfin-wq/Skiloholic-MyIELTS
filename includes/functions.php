<?php

function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
        return $input;
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function flash($key, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_' . $key] = ['message' => $message, 'type' => $type];
    } else {
        if (isset($_SESSION['flash_' . $key])) {
            $flash = $_SESSION['flash_' . $key];
            unset($_SESSION['flash_' . $key]);
            return '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">
                        ' . $flash['message'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        }
    }
    return '';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function generateVerificationCode($length = 6) {
    return substr(str_shuffle("0123456789"), 0, $length);
}

function format_date($date_string) {
    return date("F j, Y, g:i a", strtotime($date_string));
}

function get_profile_pic($pic_path) {
    if ($pic_path && file_exists(__DIR__ . '/../' . $pic_path)) {
        return $pic_path;
    }
    return 'assets/images/default_avatar.png'; // Placeholder needed
}
?>