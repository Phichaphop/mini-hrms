<?php
// /db/Localization.php
// Localization and Multi-language Support System

require_once __DIR__ . '/Database.php';

class Localization {
    private $db;
    private $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get localized text by key
     * @param string $key_id
     * @param string|null $lang
     * @return string
     */
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
    
    /**
     * Save or update localization text
     * @param string $key_id
     * @param string $th_text
     * @param string $en_text
     * @param string $my_text
     * @param string $category
     * @return bool
     */
    public function saveText($key_id, $th_text, $en_text, $my_text, $category) {
        try {
            // Check if key exists
            $existing = $this->db->fetchOne("SELECT key_id FROM localization_master WHERE key_id = ?", [$key_id]);
            
            if ($existing) {
                // Update existing
                $sql = "UPDATE localization_master 
                        SET th_text = ?, en_text = ?, my_text = ?, category = ? 
                        WHERE key_id = ?";
                $this->db->query($sql, [$th_text, $en_text, $my_text, $category, $key_id]);
            } else {
                // Insert new
                $sql = "INSERT INTO localization_master (key_id, th_text, en_text, my_text, category) 
                        VALUES (?, ?, ?, ?, ?)";
                $this->db->query($sql, [$key_id, $th_text, $en_text, $my_text, $category]);
            }
            
            // Clear cache
            unset($this->cache[$key_id . '_th']);
            unset($this->cache[$key_id . '_en']);
            unset($this->cache[$key_id . '_my']);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete localization text
     * @param string $key_id
     * @return bool
     */
    public function deleteText($key_id) {
        try {
            $sql = "DELETE FROM localization_master WHERE key_id = ?";
            $this->db->query($sql, [$key_id]);
            
            // Clear cache
            unset($this->cache[$key_id . '_th']);
            unset($this->cache[$key_id . '_en']);
            unset($this->cache[$key_id . '_my']);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get all texts by category
     * @param string|null $category
     * @return array
     */
    public function getAllTexts($category = null) {
        try {
            if ($category) {
                $sql = "SELECT * FROM localization_master WHERE category = ? ORDER BY key_id";
                return $this->db->fetchAll($sql, [$category]);
            } else {
                $sql = "SELECT * FROM localization_master ORDER BY category, key_id";
                return $this->db->fetchAll($sql);
            }
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get all categories
     * @return array
     */
    public function getAllCategories() {
        try {
            $sql = "SELECT DISTINCT category FROM localization_master WHERE category IS NOT NULL ORDER BY category";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Clear all cache
     */
    public function clearCache() {
        $this->cache = [];
    }
}

/**
 * Global helper function to get localized text
 * CRITICAL: This function must always be available
 * @param string $key_id
 * @param string|null $lang
 * @return string
 */
function get_text($key_id, $lang = null) {
    static $localization = null;
    if ($localization === null) {
        $localization = new Localization();
    }
    return $localization->getText($key_id, $lang);
}
?>