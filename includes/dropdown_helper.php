<?php
// /includes/dropdown_helper.php
// Complete Helper functions for multilingual dropdowns

require_once __DIR__ . '/../db/Localization.php';

/**
 * Get current user language
 */
function getCurrentLanguage() {
    return $_SESSION['user_language'] ?? 'en';
}

/**
 * Get prefix options
 */
function getPrefixOptions($db, $selectedId = null) {
    $lang = getCurrentLanguage();
    $prefixes = $db->fetchAll("SELECT * FROM prefix_master ORDER BY prefix_id");
    
    $html = '<option value="">Select Prefix</option>';
    foreach ($prefixes as $prefix) {
        $selected = ($selectedId == $prefix['prefix_id']) ? 'selected' : '';
        $name = $prefix["prefix_name_{$lang}"] ?? $prefix['prefix_name_en'];
        $html .= "<option value='{$prefix['prefix_id']}' {$selected}>" . htmlspecialchars($name) . "</option>";
    }
    return $html;
}

/**
 * Get operation options
 */
function getOperationOptions($db, $selectedValue = null) {
    $operations = $db->fetchAll("SELECT * FROM operation_master ORDER BY operation_name");
    
    $html = '<option value="">Select Operation</option>';
    foreach ($operations as $operation) {
        $selected = ($selectedValue === $operation['operation_name']) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($operation['operation_name']) . "' {$selected}>" . htmlspecialchars($operation['operation_name']) . "</option>";
    }
    return $html;
}

/**
 * Get hiring type options with multilingual support
 */
function getHiringTypeOptions($db, $selectedValue = null) {
    $lang = getCurrentLanguage();
    $types = $db->fetchAll("SELECT * FROM hiring_type_master ORDER BY hiring_type_id");
    
    $html = '<option value="">Select Hiring Type</option>';
    foreach ($types as $type) {
        $name = $type["hiring_type_name_{$lang}"] ?? $type['hiring_type_name_en'];
        $value = $type['hiring_type_name_en']; // Store English name as value
        $selected = ($selectedValue === $value) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($value) . "' {$selected}>" . htmlspecialchars($name) . "</option>";
    }
    return $html;
}

/**
 * Get customer zone options
 */
function getCustomerZoneOptions($db, $selectedValue = null) {
    $zones = $db->fetchAll("SELECT * FROM customer_zone_master ORDER BY zone_name");
    
    $html = '<option value="">Select Customer Zone</option>';
    foreach ($zones as $zone) {
        $selected = ($selectedValue === $zone['zone_name']) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($zone['zone_name']) . "' {$selected}>" . htmlspecialchars($zone['zone_name']) . "</option>";
    }
    return $html;
}

/**
 * Get contribution level options
 */
function getContributionLevelOptions($db, $selectedValue = null) {
    $levels = $db->fetchAll("SELECT * FROM contribution_level_master ORDER BY level_name");
    
    $html = '<option value="">Select Contribution Level</option>';
    foreach ($levels as $level) {
        $selected = ($selectedValue === $level['level_name']) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($level['level_name']) . "' {$selected}>" . htmlspecialchars($level['level_name']) . "</option>";
    }
    return $html;
}

/**
 * Get leave type options with multilingual support
 */
function getLeaveTypeOptions($db, $selectedValue = null) {
    $lang = getCurrentLanguage();
    $types = $db->fetchAll("SELECT * FROM leave_type_master ORDER BY leave_type_id");
    
    $html = '<option value="">Select Leave Type</option>';
    foreach ($types as $type) {
        $name = $type["leave_type_name_{$lang}"] ?? $type['leave_type_name_en'];
        $value = $type['leave_type_name_en']; // Store English name as value
        $selected = ($selectedValue === $value) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($value) . "' {$selected}>" . htmlspecialchars($name) . "</option>";
    }
    return $html;
}

/**
 * Get function options
 */
function getFunctionOptions($db, $selectedId = null) {
    $functions = $db->fetchAll("SELECT * FROM function_master ORDER BY function_name");
    
    $html = '<option value="">Select Function</option>';
    foreach ($functions as $function) {
        $selected = ($selectedId == $function['function_id']) ? 'selected' : '';
        $html .= "<option value='{$function['function_id']}' {$selected}>" . htmlspecialchars($function['function_name']) . "</option>";
    }
    return $html;
}

/**
 * Get division options
 */
function getDivisionOptions($db, $selectedId = null) {
    $divisions = $db->fetchAll("SELECT * FROM division_master ORDER BY division_name");
    
    $html = '<option value="">Select Division</option>';
    foreach ($divisions as $division) {
        $selected = ($selectedId == $division['division_id']) ? 'selected' : '';
        $html .= "<option value='{$division['division_id']}' {$selected}>" . htmlspecialchars($division['division_name']) . "</option>";
    }
    return $html;
}

/**
 * Get department options
 */
function getDepartmentOptions($db, $selectedId = null) {
    $departments = $db->fetchAll("SELECT * FROM department_master ORDER BY department_name");
    
    $html = '<option value="">Select Department</option>';
    foreach ($departments as $dept) {
        $selected = ($selectedId == $dept['department_id']) ? 'selected' : '';
        $html .= "<option value='{$dept['department_id']}' {$selected}>" . htmlspecialchars($dept['department_name']) . "</option>";
    }
    return $html;
}

/**
 * Get section options
 */
function getSectionOptions($db, $selectedId = null) {
    $sections = $db->fetchAll("SELECT * FROM section_master ORDER BY section_name");
    
    $html = '<option value="">Select Section</option>';
    foreach ($sections as $section) {
        $selected = ($selectedId == $section['section_id']) ? 'selected' : '';
        $html .= "<option value='{$section['section_id']}' {$selected}>" . htmlspecialchars($section['section_name']) . "</option>";
    }
    return $html;
}

/**
 * Get position options
 */
function getPositionOptions($db, $selectedId = null) {
    $positions = $db->fetchAll("SELECT * FROM position_master ORDER BY position_name");
    
    $html = '<option value="">Select Position</option>';
    foreach ($positions as $position) {
        $selected = ($selectedId == $position['position_id']) ? 'selected' : '';
        $html .= "<option value='{$position['position_id']}' {$selected}>" . htmlspecialchars($position['position_name']) . "</option>";
    }
    return $html;
}

/**
 * Get position level options
 */
function getPositionLevelOptions($db, $selectedValue = null) {
    $levels = $db->fetchAll("SELECT * FROM position_level_master ORDER BY level_name");
    
    $html = '<option value="">Select Position Level</option>';
    foreach ($levels as $level) {
        $selected = ($selectedValue === $level['level_name']) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($level['level_name']) . "' {$selected}>" . htmlspecialchars($level['level_name']) . "</option>";
    }
    return $html;
}

/**
 * Get education level options
 */

function getEducationLevelOptions($db, $selectedValue = null) {
    $levels = $db->fetchAll("SELECT * FROM education_level_master ORDER BY education_name");
    
    $html = '<option value="">Select Education Level</option>';
    foreach ($levels as $level) {
        $selected = ($selectedValue === $level['education_name']) ? 'selected' : '';
        $html .= "<option value='" . htmlspecialchars($level['education_name']) . "' {$selected}>" . htmlspecialchars($level['education_name']) . "</option>";
    }
    return $html;
}

/**
 * Get nationality options with multilingual support
 */
function getNationalityOptions($selectedValue = null) {
    $lang = getCurrentLanguage();
    
    $nationalities = [
        'Thai' => ['th' => 'ไทย', 'en' => 'Thai', 'my' => 'ထိုင်း'],
        'Myanmar' => ['th' => 'พม่า', 'en' => 'Myanmar', 'my' => 'မြန်မာ'],
        'Lao' => ['th' => 'ลาว', 'en' => 'Lao', 'my' => 'လာအို'],
        'Cambodian' => ['th' => 'กัมพูชา', 'en' => 'Cambodian', 'my' => 'ကမ္ဘောဒီးယား'],
        'Vietnamese' => ['th' => 'เวียดนาม', 'en' => 'Vietnamese', 'my' => 'ဗီယက်နမ်'],
        'Malaysian' => ['th' => 'มาเลเซีย', 'en' => 'Malaysian', 'my' => 'မလေးရှား'],
        'Singaporean' => ['th' => 'สิงคโปร์', 'en' => 'Singaporean', 'my' => 'စင်ကာပူ'],
        'Filipino' => ['th' => 'ฟิลิปปินส์', 'en' => 'Filipino', 'my' => 'ဖိလစ်ပိုင်'],
        'Indonesian' => ['th' => 'อินโดนีเซีย', 'en' => 'Indonesian', 'my' => 'အင်ဒိုနီးရှား'],
        'American' => ['th' => 'อเมริกัน', 'en' => 'American', 'my' => 'အမေရိကန်'],
        'British' => ['th' => 'อังกฤษ', 'en' => 'British', 'my' => 'ဗြိတိသျှ'],
        'Chinese' => ['th' => 'จีน', 'en' => 'Chinese', 'my' => 'တရုတ်'],
        'Japanese' => ['th' => 'ญี่ปุ่น', 'en' => 'Japanese', 'my' => 'ဂျပန်'],
        'Korean' => ['th' => 'เกาหลี', 'en' => 'Korean', 'my' => 'ကိုရီးယား'],
        'Indian' => ['th' => 'อินเดีย', 'en' => 'Indian', 'my' => 'အိန္ဒိယ'],
        'Other' => ['th' => 'อื่นๆ', 'en' => 'Other', 'my' => 'အခြား']
    ];
    
    $html = '<option value="">Select Nationality</option>';
    foreach ($nationalities as $value => $names) {
        $selected = ($selectedValue === $value) ? 'selected' : '';
        $displayName = $names[$lang] ?? $names['en'];
        $html .= "<option value='{$value}' {$selected}>" . htmlspecialchars($displayName) . "</option>";
    }
    return $html;
}

/**
 * Get sex options with multilingual support
 */
function getSexOptions($selectedValue = null) {
    $lang = getCurrentLanguage();
    
    $sexOptions = [
        'Male' => ['th' => 'ชาย', 'en' => 'Male', 'my' => 'ကျား'],
        'Female' => ['th' => 'หญิง', 'en' => 'Female', 'my' => 'မ'],
        'Other' => ['th' => 'อื่นๆ', 'en' => 'Other', 'my' => 'အခြား']
    ];
    
    $html = '<option value="">Select Sex</option>';
    foreach ($sexOptions as $value => $names) {
        $selected = ($selectedValue === $value) ? 'selected' : '';
        $displayName = $names[$lang] ?? $names['en'];
        $html .= "<option value='{$value}' {$selected}>" . htmlspecialchars($displayName) . "</option>";
    }
    return $html;
}

/**
 * Get status options with multilingual support
 */
function getStatusOptions($selectedValue = null) {
    $lang = getCurrentLanguage();
    
    $statusOptions = [
        'Active' => ['th' => 'ทำงานอยู่', 'en' => 'Active', 'my' => 'တက်ကြွ'],
        'Inactive' => ['th' => 'ไม่ทำงาน', 'en' => 'Inactive', 'my' => 'မလှုပ်ရှား'],
        'Terminated' => ['th' => 'ออกจากงาน', 'en' => 'Terminated', 'my' => 'ရပ်ဆိုင်း']
    ];
    
    $html = '';
    foreach ($statusOptions as $value => $names) {
        $selected = ($selectedValue === $value) ? 'selected' : '';
        $displayName = $names[$lang] ?? $names['en'];
        $html .= "<option value='{$value}' {$selected}>" . htmlspecialchars($displayName) . "</option>";
    }
    return $html;
}

/**
 * Get role options
 */
function getRoleOptions($db, $selectedId = null) {
    $roles = $db->fetchAll("SELECT * FROM roles ORDER BY role_name");
    
    $html = '';
    foreach ($roles as $role) {
        $selected = ($selectedId == $role['role_id']) ? 'selected' : '';
        $html .= "<option value='{$role['role_id']}' {$selected}>" . htmlspecialchars($role['role_name']) . "</option>";
    }
    return $html;
}

/**
 * Get service category options with multilingual support
 */
function getServiceCategoryOptions($db, $selectedId = null) {
    $lang = getCurrentLanguage();
    $categories = $db->fetchAll("SELECT * FROM service_category_master ORDER BY category_id");
    
    $html = '<option value="">Select Category</option>';
    foreach ($categories as $category) {
        $selected = ($selectedId == $category['category_id']) ? 'selected' : '';
        $name = $category["category_name_{$lang}"] ?? $category['category_name_en'];
        $html .= "<option value='{$category['category_id']}' {$selected}>" . htmlspecialchars($name) . "</option>";
    }
    return $html;
}

/**
 * Get service type options with multilingual support
 */
function getServiceTypeOptions($db, $selectedId = null) {
    $lang = getCurrentLanguage();
    $types = $db->fetchAll("SELECT * FROM service_type_master ORDER BY type_id");
    
    $html = '<option value="">Select Type</option>';
    foreach ($types as $type) {
        $selected = ($selectedId == $type['type_id']) ? 'selected' : '';
        $name = $type["type_name_{$lang}"] ?? $type['type_name_en'];
        $html .= "<option value='{$type['type_id']}' {$selected}>" . htmlspecialchars($name) . "</option>";
    }
    return $html;
}
?>