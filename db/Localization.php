<?php
// /db/Localization.php

require_once __DIR__ . '/Database.php';

class Localization {
    private $db;
    private $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getText($key_id, $lang = null) {
        if ($lang === null && isset($_SESSION['user_language'])) {
            $lang = $_SESSION['user_language'];
        }
        
        if ($lang === null) {
            $lang = DEFAULT_LANGUAGE;
        }
        
        $cacheKey = $key_id . '_' . $lang;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            $column = $lang . '_text';
            $sql = "SELECT `$column` FROM `localization_master` WHERE `key_id` = ?";
            $result = $this->db->fetchOne($sql, [$key_id]);
            
            if ($result && !empty($result[$column])) {
                $text = $result[$column];
                $this->cache[$cacheKey] = $text;
                return $text;
            }
            
            return $key_id;
        } catch (Exception $e) {
            return $key_id;
        }
    }
}

// CRITICAL: ฟังก์ชันนี้ต้องมี!
function get_text($key_id, $lang = null) {
    static $localization = null;
    if ($localization === null) {
        $localization = new Localization();
    }
    return $localization->getText($key_id, $lang);
}
?>