<?php
/**
 * Upgrade script for Invoice Notes module from 1.0.0 to 1.1.0
 * Path: modules/invoicenotes/upgrade/upgrade-1.1.0.php
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    // Add the new note_title column to existing installations
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'invoice_notes` 
            ADD COLUMN `note_title` varchar(255) NOT NULL DEFAULT "Special Notes:" 
            AFTER `id_order`';
    
    $result = Db::getInstance()->execute($sql);
    
    if (!$result) {
        // Check if column already exists (avoid error on multiple upgrade attempts)
        $check_sql = 'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'invoice_notes` LIKE "note_title"';
        $column_exists = Db::getInstance()->getValue($check_sql);
        
        if (!$column_exists) {
            return false;
        }
    }
    
    return true;
}