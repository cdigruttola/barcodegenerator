<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use cdigruttola\Barcodegenerator\Form\DataConfiguration\BarcodeConfigurationData;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Barcodegenerator extends Module
{
    public function __construct()
    {
        $this->name = 'barcodegenerator';
        $this->tab = 'market_place';
        $this->version = '2.0.0';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Barcode Generator', [], 'Modules.Barcodegenerator.Main');
        $this->description = $this->trans('A module to generate barcode', [], 'Modules.Barcodegenerator.Main');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Barcodegenerator.Main');

        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function install()
    {
        $this->_clearCache('*');

        return parent::install();
    }

    public function uninstall()
    {
        Configuration::deleteByName(BarcodeConfigurationData::BARCODEGENERATOR_COUNTRY_PREFIX);
        Configuration::deleteByName(BarcodeConfigurationData::BARCODEGENERATOR_COMPANY_PREFIX);
        Configuration::deleteByName(BarcodeConfigurationData::BARCODEGENERATOR_REPLACE_CODE);
        Configuration::deleteByName(BarcodeConfigurationData::BARCODEGENERATOR_CUSTOM_ID);
        Configuration::deleteByName(BarcodeConfigurationData::BARCODEGENERATOR_ID_PRODUCT_OR_CUSTOM_ID);

        return parent::uninstall();
    }

    public function getContent()
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('barcode_controller'));
    }
}
