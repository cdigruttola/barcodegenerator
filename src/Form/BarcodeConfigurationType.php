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

namespace cdigruttola\Barcodegenerator\Form;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class BarcodeConfigurationType extends TranslatorAwareType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('replace_code', SwitchType::class, [
                'required' => true,
                'label' => $this->trans('Replace current codes?', 'Modules.Barcodegenerator.Main'),
            ])
            ->add('product_or_custom_id', SwitchType::class, [
                'required' => true,
                'label' => $this->trans('Use Product ID or the custom ID value', 'Modules.Barcodegenerator.Main'),
                'choices' => [
                    $this->trans('Custom ID', 'Modules.Barcodegenerator.Main') => false,
                    $this->trans('Product ID', 'Modules.Barcodegenerator.Main') => true,
                ],
            ])
            ->add('custom_id', NumberType::class, [
                'required' => false,
                'label' => $this->trans('Custom value to use for generation', 'Modules.Barcodegenerator.Main'),
                'help' => $this->trans('Set this field only if you choose custom ID. This value will be automatically update during generation', 'Modules.Barcodegenerator.Main'),
            ])
            ->add('country_prefix', NumberType::class, [
                'required' => true,
                'label' => $this->trans('Country Prefix', 'Modules.Barcodegenerator.Main'),
                'help' => $this->trans('Enter the prefix for EAN (the first three digits of your GS1 prefix)', 'Modules.Barcodegenerator.Main'),
            ])
            ->add('company_prefix', NumberType::class, [
                'required' => true,
                'label' => $this->trans('Company Prefix', 'Modules.Barcodegenerator.Main'),
                'help' => $this->trans('Enter the company prefix for EAN (the remaining digits after the first three)', 'Modules.Barcodegenerator.Main'),
            ]);
    }
}
