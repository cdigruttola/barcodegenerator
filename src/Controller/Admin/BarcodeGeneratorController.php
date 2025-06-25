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

namespace cdigruttola\Barcodegenerator\Controller\Admin;

use cdigruttola\Barcodegenerator\Service\Admin\BarcodeGeneratorService;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Controller responsible for barcode generation.
 */
class BarcodeGeneratorController extends PrestashopAdminController
{
    /** @var \Barcodegenerator */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function index(
        #[Autowire(service: 'cdigruttola.barcodegenerator.form.configuration_type.form_handler')]
        FormHandlerInterface $formHandler,
    ): Response {
        $configurationForm = $formHandler->getForm();

        return $this->render('@Modules/barcodegenerator/views/templates/admin/index.html.twig', [
            'form' => $configurationForm->createView(),
            'module_dir' => _MODULE_DIR_ . $this->module->name . '/',
            'help_link' => false,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveConfiguration(
        Request $request,
        #[Autowire(service: 'cdigruttola.barcodegenerator.form.configuration_type.form_handler')]
        FormHandlerInterface $formHandler,
    ): Response {
        $redirectResponse = $this->redirectToRoute('barcode_controller');

        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $formHandler->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->addFlashErrors($formErrors);

        return $redirectResponse;
    }

    /**
     * #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     *
     * @return JsonResponse
     *
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function generateAction(
        #[Autowire(service: 'cdigruttola.barcodegenerator.admin.barcode_generator_service')]
        BarcodeGeneratorService $generator_service,
    ): Response {
        $redirectResponse = $this->redirectToRoute('barcode_controller');
        try {
            if ($generator_service->generateAndFill()) {
                $this->addFlash('success', $this->trans('Barcodes succesfully created', [], 'Modules.Barcodegenerator.Main'));
            } else {
                $this->addFlash('error', $this->trans('An error occurred during barcode generation, please check if country and company prefixes are set', [], 'Modules.Barcodegenerator.Error'));
            }
        } catch (InvalidArgumentException $ex) {
            $this->addFlash('error', $this->trans('An error occurred during barcode generation, due to limit implementation you can only generate EAN 13 codes for combination products where sum of id length for base product and length of digits of combinations number does not exceed 12 minus sum of length of prefixes. Error message %s', [$ex->getMessage()], 'Modules.Barcodegenerator.Error'));
        }

        return $redirectResponse;
    }
}
