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
if (!defined('_PS_VERSION_')) {
    exit;
}

class Barcodegenerator extends Module
{
    public const BARCODEGENERATOR_EAN = 'BARCODEGENERATOR_EAN';
    public const BARCODEGENERATOR_REPLACE_CODE = 'BARCODEGENERATOR_REPLACE_CODE';
    public const BARCODEGENERATOR_COUNTRY_PREFIX = 'BARCODEGENERATOR_COUNTRY_PREFIX';
    public const BARCODEGENERATOR_COMPANY_PREFIX = 'BARCODEGENERATOR_COMPANY_PREFIX';
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'barcodegenerator';
        $this->tab = 'market_place';
        $this->version = '1.0.3';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;
        $this->module_key = '05df11732203e6e6bcdb690348257aa7';

        /*
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Barcode Generator', [], 'Modules.Barcodegenerator.Main');
        $this->description = $this->trans('A module to generate barcode', [], 'Modules.Barcodegenerator.Main');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Barcodegenerator.Main');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $this->_clearCache('*');

        return parent::install() && $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::BARCODEGENERATOR_COUNTRY_PREFIX);
        Configuration::deleteByName(self::BARCODEGENERATOR_COMPANY_PREFIX);
        Configuration::deleteByName(self::BARCODEGENERATOR_EAN);
        Configuration::deleteByName(self::BARCODEGENERATOR_REPLACE_CODE);

        return parent::uninstall();
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $link = new Link();
            $symfonyUrl = $link->getAdminLink('BarcodeGenerator', true, ['route' => 'barcode_generate']);

            Media::addJsDef(
                [
                    'ajax_link' => $symfonyUrl,
                ]);
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $output = '';
        if (Tools::isSubmit('submitBarcodegeneratorModule')) {
            if ($this->postProcess()) {
                $output .= $this->displayConfirmation($this->trans('Settings updated succesfully', [], 'Modules.Barcodegenerator.Main'));
            } else {
                $output .= $this->displayError($this->trans('Error occurred during settings update', [], 'Modules.Barcodegenerator.Main'));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm() . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/barcode_generate.tpl');
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBarcodegeneratorModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Settings', [], 'Modules.Barcodegenerator.Main'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Barcode Generator EAN', [], 'Modules.Barcodegenerator.Main'),
                        'name' => self::BARCODEGENERATOR_EAN,
                        'is_bool' => true,
                        'desc' => $this->trans('Use this module to generate EAN codes', [], 'Modules.Barcodegenerator.Main'),
                        'values' => [
                            [
                                'id' => 'ean_on',
                                'value' => true,
                                'label' => $this->trans('Enabled', [], 'Modules.Barcodegenerator.Main'),
                            ],
                            [
                                'id' => 'ean_off',
                                'value' => false,
                                'label' => $this->trans('Disabled', [], 'Modules.Barcodegenerator.Main'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Replace current codes?', [], 'Modules.Barcodegenerator.Main'),
                        'name' => self::BARCODEGENERATOR_REPLACE_CODE,
                        'is_bool' => true,
                        'desc' => $this->trans('Do you want to replace the EAN?', [], 'Modules.Barcodegenerator.Main'),
                        'values' => [
                            [
                                'id' => 'replace_on',
                                'value' => true,
                                'label' => $this->trans('Enabled', [], 'Modules.Barcodegenerator.Main'),
                            ],
                            [
                                'id' => 'replace_off',
                                'value' => false,
                                'label' => $this->trans('Disabled', [], 'Modules.Barcodegenerator.Main'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Country Prefix', [], 'Modules.Barcodegenerator.Main'),
                        'desc' => $this->trans('Enter the prefix for EAN (the first three digits of your GS1 prefix)', [], 'Modules.Barcodegenerator.Main'),
                        'name' => self::BARCODEGENERATOR_COUNTRY_PREFIX,
                        'required' => true,
                        'validation' => 'isInt',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Company Prefix', [], 'Modules.Barcodegenerator.Main'),
                        'desc' => $this->trans('Enter the company prefix for EAN (the remaining digits after the first three)', [], 'Modules.Barcodegenerator.Main'),
                        'name' => self::BARCODEGENERATOR_COMPANY_PREFIX,
                        'required' => true,
                        'validation' => 'isInt',
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.Barcodegenerator.Main'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $id_shop = (int) $this->context->shop->id;

        return [
            self::BARCODEGENERATOR_EAN => Configuration::get(self::BARCODEGENERATOR_EAN, null, null, $id_shop),
            self::BARCODEGENERATOR_REPLACE_CODE => Configuration::get(self::BARCODEGENERATOR_REPLACE_CODE, null, null, $id_shop),
            self::BARCODEGENERATOR_COUNTRY_PREFIX => Configuration::get(self::BARCODEGENERATOR_COUNTRY_PREFIX, null, null, $id_shop),
            self::BARCODEGENERATOR_COMPANY_PREFIX => Configuration::get(self::BARCODEGENERATOR_COMPANY_PREFIX, null, null, $id_shop),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $country_prefix = Tools::getValue(self::BARCODEGENERATOR_COUNTRY_PREFIX);
        $company_prefix = Tools::getValue(self::BARCODEGENERATOR_COMPANY_PREFIX);
        if (empty($country_prefix) || !Validate::isInt($country_prefix) || empty($company_prefix) || !Validate::isInt($company_prefix)) {
            return false;
        }

        $res = true;
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            $res &= Configuration::updateValue($key, Tools::getValue($key));
        }

        return $res;
    }
}
