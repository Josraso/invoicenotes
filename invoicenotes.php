<?php
/**
 * Invoice Notes Module for PrestaShop 1.7 & 8
 * Allows adding individual notes to orders that appear on invoices
 *
 * @author Tu Nombre
 * @version 1.1.0
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class InvoiceNotes extends Module
{
    public function __construct()
    {
        $this->name = 'invoicenotes';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Tu Nombre';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        // Use modern translations for PS 1.7.6+ or fallback to classic
        if (method_exists($this, 'trans')) {
            $this->displayName = $this->trans('Invoice Notes', array(), 'Modules.Invoicenotes.Admin');
            $this->description = $this->trans('Add individual notes to orders that will appear on invoices', array(), 'Modules.Invoicenotes.Admin');
            $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?', array(), 'Modules.Invoicenotes.Admin');
        } else {
            $this->displayName = $this->l('Invoice Notes');
            $this->description = $this->l('Add individual notes to orders that will appear on invoices');
            $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install() &&
            $this->installDb() &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayPDFInvoice') &&
            $this->registerHook('actionOrderStatusPostUpdate');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDb();
    }

    public function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'invoice_notes` (
            `id_invoice_note` int(11) NOT NULL AUTO_INCREMENT,
            `id_order` int(11) NOT NULL,
            `note_title` varchar(255) NOT NULL DEFAULT "Special Notes:",
            `note` text NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_invoice_note`),
            UNIQUE KEY `unique_order` (`id_order`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    public function uninstallDb()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'invoice_notes`';
        return Db::getInstance()->execute($sql);
    }

    public function hookDisplayAdminOrder($params)
    {
        $id_order = (int)$params['id_order'];
        $note_data = $this->getOrderNoteData($id_order);

        if (Tools::isSubmit('submitInvoiceNote')) {
            $note_title = Tools::getValue('invoice_note_title');
            $note_text = Tools::getValue('invoice_note');
            $this->saveOrderNote($id_order, $note_title, $note_text);
            $note_data = array('title' => $note_title, 'note' => $note_text);
            
            // Mostrar mensaje de éxito
            if (method_exists($this, 'trans')) {
                $this->context->controller->confirmations[] = $this->trans('Note saved successfully', array(), 'Modules.Invoicenotes.Admin');
            } else {
                $this->context->controller->confirmations[] = $this->l('Note saved successfully');
            }
        }

        $default_title = method_exists($this, 'trans') ? 
            $this->trans('Special Notes:', array(), 'Modules.Invoicenotes.Admin') : 
            $this->l('Special Notes:');

        $this->context->smarty->assign(array(
            'invoice_note_title' => isset($note_data['title']) ? $note_data['title'] : $default_title,
            'invoice_note' => isset($note_data['note']) ? $note_data['note'] : '',
            'id_order' => $id_order,
            'module_dir' => $this->_path
        ));

        return $this->display(__FILE__, 'views/templates/admin/order_note.tpl');
    }

    public function hookDisplayPDFInvoice($params)
    {
        if (!isset($params['object']) || !is_object($params['object'])) {
            return '';
        }

        $id_order = (int)$params['object']->id_order;
        $note_data = $this->getOrderNoteData($id_order);

        if (!empty($note_data['note'])) {
            $default_title = method_exists($this, 'trans') ? 
                $this->trans('Special Notes:', array(), 'Modules.Invoicenotes.Admin') : 
                $this->l('Special Notes:');
            
            $title = !empty($note_data['title']) ? $note_data['title'] : $default_title;
            
            return '<div style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
                <strong>' . htmlspecialchars($title) . '</strong><br/><br/>
                ' . nl2br(htmlspecialchars($note_data['note'])) . '
            </div>';
        }

        return '';
    }

    private function getOrderNoteData($id_order)
    {
        $sql = 'SELECT `note_title`, `note` FROM `' . _DB_PREFIX_ . 'invoice_notes` 
                WHERE `id_order` = ' . (int)$id_order;
        
        $result = Db::getInstance()->getRow($sql);
        
        if ($result) {
            return array(
                'title' => $result['note_title'],
                'note' => $result['note']
            );
        }
        
        return array('title' => '', 'note' => '');
    }

    private function getOrderNote($id_order)
    {
        $data = $this->getOrderNoteData($id_order);
        return $data['note'];
    }

    private function saveOrderNote($id_order, $note_title, $note)
    {
        $existing = $this->getOrderNoteData($id_order);
        $date = date('Y-m-d H:i:s');

        if (!empty($existing['note']) || !empty($existing['title'])) {
            // Update existing note
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'invoice_notes` 
                    SET `note_title` = "' . pSQL($note_title) . '", 
                        `note` = "' . pSQL($note) . '", 
                        `date_upd` = "' . $date . '" 
                    WHERE `id_order` = ' . (int)$id_order;
        } else {
            // Insert new note
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'invoice_notes` 
                    (`id_order`, `note_title`, `note`, `date_add`, `date_upd`) 
                    VALUES (' . (int)$id_order . ', "' . pSQL($note_title) . '", "' . pSQL($note) . '", "' . $date . '", "' . $date . '")';
        }

        return Db::getInstance()->execute($sql);
    }
}