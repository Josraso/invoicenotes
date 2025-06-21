<?php
/**
 * Security file to prevent direct access
 * Create this file in each directory of the module
 * 
 * Paths needed:
 * - modules/invoicenotes/index.php
 * - modules/invoicenotes/views/index.php
 * - modules/invoicenotes/views/templates/index.php
 * - modules/invoicenotes/views/templates/admin/index.php
 * - modules/invoicenotes/translations/index.php
 */

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

header('Location: ../');
exit;