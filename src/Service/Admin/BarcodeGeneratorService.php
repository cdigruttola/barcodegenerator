<?php

namespace cdigruttola\Module\Barcodegenerator\Service\Admin;

use Db;
use PrestaShop\PrestaShop\Core\Domain\Module\Exception\ModuleException;
use PrestaShop\PrestaShop\Core\Domain\Module\Exception\ModuleNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Shop\Exception\ShopException;
use Product;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;

class BarcodeGeneratorService
{
    /**
     * @var false|\Module
     */
    private $module;
    private ?int $id_shop;

    /**
     * @throws ModuleException
     * @throws ModuleNotFoundException
     * @throws ShopException
     */
    public function __construct()
    {
        $this->module = \Module::getInstanceByName('barcodegenerator');
        if (!$this->module) {
            throw new ModuleNotFoundException();
        }
        if (!$this->module->active) {
            throw new ModuleException();
        }
        $this->id_shop = \Shop::getContextShopID();
        if (!isset($this->id_shop)) {
            throw new ShopException('No shop id in Context');
        }
    }

    /**
     * Used to generate and fill the ean13 of ps_product db table
     *
     * @throws \PrestaShopException
     */
    public function generateAndFill(): bool
    {
        $replace_existing = \Configuration::get(\Barcodegenerator::BARCODEGENERATOR_REPLACE_CODE);

        $sql = 'SELECT `id_product`, `ean13` FROM `' . _DB_PREFIX_ . 'product` ';
        if (!$replace_existing) {
            $sql .= ' WHERE `ean13` = "" OR `ean13` IS NULL';
        }

        $raw_products = \Db::getInstance()->executeS($sql);

        foreach ($raw_products as $raw_product) {
            $product = new \Product($raw_product['id_product']);

            if (\Configuration::get(\Barcodegenerator::BARCODEGENERATOR_EAN) && ($replace_existing || !$raw_product['ean13'])) {
                $ean = $this->genEAN($product->id);
                if (!$ean) {
                    return false;
                }
                $product->ean13 = $ean;
                $product->update();

                // Variants EAN calculation
                $attributeIds = \Product::getProductAttributesIds($product->id);
                if (!empty($attributeIds)) {
                    for ($i = 0; $i < count($attributeIds); ++$i) {
                        $combination = new \CombinationCore($attributeIds[$i]['id_product_attribute']);
                        $ean = $this->genEAN($product->id, $i + 1);
                        if (!$ean) {
                            return false;
                        }
                        $combination->ean13 = $ean;
                        $combination->update();
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $id_base
     * @param int $id_variant
     *
     * @return false|string
     */
    public function genEAN($id_base, int $id_variant = -1)
    {
        if (\Configuration::get(\Barcodegenerator::BARCODEGENERATOR_COUNTRY_PREFIX) && \Configuration::get(\Barcodegenerator::BARCODEGENERATOR_COMPANY_PREFIX)) {
            $country_prefix = \Configuration::get(\Barcodegenerator::BARCODEGENERATOR_COUNTRY_PREFIX);
            $company_prefix = \Configuration::get(\Barcodegenerator::BARCODEGENERATOR_COMPANY_PREFIX);
        } else {
            return false;
        }

        $prefix = $country_prefix . $company_prefix;
        $prefixLen = strlen($prefix);
        if ($id_variant !== -1) { // variant product
            $baseLen = strlen($id_base);
            $variantLen = strlen((string) $id_variant);
            if ($baseLen + $variantLen > 12 - $prefixLen) {
                throw new InvalidArgumentException('ID base product affected: ' . $id_base);
            }

            $zeroEANLen = 12 - $prefixLen - $variantLen; /* variant number */
            $ean = $prefix . $id_variant . str_pad($id_base, $zeroEANLen, '0', STR_PAD_LEFT);
        } else { // base product
            $zeroEANLen = 12 - $prefixLen;
            $ean = $prefix . str_pad($id_base, $zeroEANLen, '0', STR_PAD_LEFT);
        }

        $sum = 0;
        for ($i = \Tools::strlen($ean) - 1; $i >= 0; --$i) {
            if ($i % 2 !== 0) {
                $sum += ($ean[$i] * 3);
            } else {
                $sum += (int) $ean[$i];
            }
        }
        $difference = (10 - ($sum % 10)) % 10;

        return $ean . $difference;
    }
}
