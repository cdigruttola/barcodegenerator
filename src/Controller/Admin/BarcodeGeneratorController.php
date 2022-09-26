<?php
/**
 * 2007-2022 Carmine Di Gruttola
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
 * @copyright 2007-2022 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace cdigruttola\Module\Barcodegenerator\Controller\Admin;

use cdigruttola\Module\Barcodegenerator\Service\Admin\BarcodeGeneratorService;
use Exception;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller responsible for barcode generation.
 */
class BarcodeGeneratorController extends FrameworkBundleAdminController
{
    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @param Request $request
     *
     * @return Response
     * @throws PrestaShopException
     * @throws Exception
     */
    public function generateAction(Request $request)
    {
        /**
         * @var $generator_service BarcodeGeneratorService
         */
        $generator_service = $this->get('cdigruttola.barcodegenerator.admin.barcode_generator_service');

        $url = $request->server->get('HTTP_REFERER');
        try {
            if ($generator_service->generateAndFill()) {
                return $this->redirect($url, Response::HTTP_CREATED);
            } else {
                throw new Exception($this->trans('An error occurred during barcode generation, please check if country and company prefixes are set', 'Modules.Barcodegenerator.Error'));
            }
        } catch (InvalidArgumentException $ex) {
            throw new Exception($this->trans('An error occurred during barcode generation, due to limit implementation you can only generate EAN 13 codes for combination products where sum of id length for base product and length of digits of combinations number does not exceed 12 minus sum of length of prefixes', 'Modules.Barcodegenerator.Error') . $ex->getMessage());
        }
    }

}
