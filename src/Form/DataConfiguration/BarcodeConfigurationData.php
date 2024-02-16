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

declare(strict_types=1);

namespace cdigruttola\Barcodegenerator\Form\DataConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BarcodeConfigurationData implements DataConfigurationInterface
{
    public const BARCODEGENERATOR_ID_PRODUCT_OR_CUSTOM_ID = 'BARCODEGENERATOR_ID_PRODUCT_OR_CUSTOM_ID';
    public const BARCODEGENERATOR_CUSTOM_ID = 'BARCODEGENERATOR_CUSTOM_ID';
    public const BARCODEGENERATOR_REPLACE_CODE = 'BARCODEGENERATOR_REPLACE_CODE';
    public const BARCODEGENERATOR_COUNTRY_PREFIX = 'BARCODEGENERATOR_COUNTRY_PREFIX';
    public const BARCODEGENERATOR_COMPANY_PREFIX = 'BARCODEGENERATOR_COMPANY_PREFIX';
    private const CONFIGURATION_FIELDS = [
        'replace_code',
        'product_or_custom_id',
        'custom_id',
        'country_prefix',
        'company_prefix',
    ];

    /** @var ConfigurationInterface */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return OptionsResolver
     */
    protected function buildResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setDefined(self::CONFIGURATION_FIELDS)
            ->setAllowedTypes('replace_code', 'bool')
            ->setAllowedTypes('product_or_custom_id', 'bool')
            ->setAllowedTypes('custom_id', 'int')
            ->setAllowedTypes('country_prefix', 'int')
            ->setAllowedTypes('company_prefix', 'int');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $return = [];

        $return['replace_code'] = $this->configuration->get(self::BARCODEGENERATOR_REPLACE_CODE) ?? false;
        $return['product_or_custom_id'] = $this->configuration->get(self::BARCODEGENERATOR_ID_PRODUCT_OR_CUSTOM_ID) ?? false;
        $return['custom_id'] = $this->configuration->get(self::BARCODEGENERATOR_CUSTOM_ID);
        $return['country_prefix'] = $this->configuration->get(self::BARCODEGENERATOR_COUNTRY_PREFIX);
        $return['company_prefix'] = $this->configuration->get(self::BARCODEGENERATOR_COMPANY_PREFIX);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            $this->configuration->set(self::BARCODEGENERATOR_REPLACE_CODE, (bool) $configuration['replace_code']);
            $this->configuration->set(self::BARCODEGENERATOR_ID_PRODUCT_OR_CUSTOM_ID, (bool) $configuration['product_or_custom_id']);
            $this->configuration->set(self::BARCODEGENERATOR_CUSTOM_ID, (int) $configuration['custom_id']);
            $this->configuration->set(self::BARCODEGENERATOR_COUNTRY_PREFIX, (int) $configuration['country_prefix']);
            $this->configuration->set(self::BARCODEGENERATOR_COMPANY_PREFIX, (int) $configuration['company_prefix']);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration)
    {
        return isset($configuration['country_prefix']) && isset($configuration['company_prefix']);
    }
}
