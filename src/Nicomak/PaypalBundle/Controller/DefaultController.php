<?php
/**
 * Contrôleur du bundle PaypalBundle
 * @package         Nicomak
 * @subpackage      PaypalBundle
 * @category        Controller
 * @author          Gilles Ortheau
 */
namespace Nicomak\PaypalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route,
    Nicomak\PaypalBundle\Entity\Payment;

class DefaultController extends Controller
{
    /**
     * Appel de paypal
     * @param   Payment     $payment
     * @return  Response
     * @Route("paypal/paiement/{ref}", name="paypal_call")
     */
    public function CallAction(Payment $payment)
    {
        $this->get('logger')->debug('[PaypalBundle] ' . __CLASS__ . '::' . __FUNCTION__);

        $itemName = ($payment->getItemName() ? $payment->getItemName() :
                $this->container->getParameter('paypal.item_name'));
        $itemNumber = ($payment->getItemNumber() ? $payment->getItemNumber() :
                $this->container->getParameter('paypal.item_number'));
        $quantity = ($payment->getQuantity() ? $payment->getQuantity() :
                $this->container->getParameter('paypal.quantity'));
        $url = ($this->container->getParameter('paypal.debug') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' :
                'https://www.paypal.com/cgi-bin/webscr');

        return $this->render('NicomakPaypalBundle:Default:call.html.twig', array(
                'urlPaypal'     => $url,
                'email'         => $this->container->getParameter('paypal.email'),
                'itemName'      => $itemName,
                'itemNumber'    => $itemNumber,
                'amount'        => $payment->getAmount(),
                'quantity'      => $quantity,
                'ref'           => $payment->getRef(),
                'urlNotify'     => $this->generateUrl('paypal_validate',
                                                      array('ref' => $payment->getRef()),
                                                      true),
                'urlReturn'     => $this->generateUrl($this->container->getParameter('paypal.confirm_route'),
                                                      array('ref' => $payment->getRef()),
                                                      true),
                'urlCancel'     => $this->generateUrl($this->container->getParameter('paypal.cancel_route'),
                                                      array('ref' => $payment->getRef()),
                                                      true),
        ));

    }


    /**
     * Action de confirmation de payement
     * @param   Payment $payment
     * @return  Response
     * @Route("paypal/validate/{ref}", name="paypal_validate")
     */
    public function ValidateAction(Payment $payment)
    {
        $this->get('logger')->debug('[PaypalBundle] ' . __CLASS__ . '::' . __FUNCTION__);

        if ($this->checkPayment($payment) && !$payment->isValid()) {
            $payment->validate();
            $payment->getCompany()->addCredits($payment->getAmount());
            $this->getDoctrine()->getManager()->flush($payment);
            $this->getDoctrine()->getManager()->flush($payment->getCompany());
        }

        return new Response();
    }

    /**
     * Vérifie que le paiement a bien été payé sur Paypal
     * @param Payment $payment
     * @return bool
     */
    private function checkPayment(Payment $payment)
    {
        $this->get('logger')->debug('[PaypalBundle] ' . __CLASS__ . '::' . __FUNCTION__);

        if (!$this->checkParams($payment)) {
            return false;
        }
        return $this->checkPaypal();
    }

    /**
     * Vérifie que les données reçues correspondent au paiement
     * @param Payment $payment
     * @return bool
     */
    private function checkParams(Payment $payment)
    {
        $this->get('logger')->debug('[PaypalBundle] ' . __CLASS__ . '::' . __FUNCTION__);

        $request = $this->getRequest();
        // Vérification de l'adresse email
        if (!$request->get('business')) {
            $this->get('logger')->error("[PaypalBundle] Aucune adresse email reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if(trim($request->get('business')) != trim($this->container->getParameter('paypal.email'))) {
            $this->get('logger')->error("[PaypalBundle] L'adresse email reçue dans la notification Paypal " .
                                        $request->get('business') . " ne correspond pas à l'adresse email " .
                                        $this->container->getParameter('paypal.email'));
            return false;

        }
        // Vérification du prix payé
        if (!$request->get('mc_gross')) {
            $this->get('logger')->error("[PaypalBundle] Aucune information de prix reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if ((float)$request->get('mc_gross') != $payment->getAmount()) {
            $this->get('logger')->error('[PaypalBundle] Le prix payé ' . (float)$request->get('mc_gross') .
                                        ' ne correspond pas au prix à payer ' . $payment->getAmount());
            return false;
        }

        // Vérification du statut
        if (!$request->get('payment_status')) {
            $this->get('logger')->error("[PaypalBundle] Aucune information de statut reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if (strtoupper($request->get('payment_status')) != 'COMPLETED') {
            $this->get('logger')->error('[PaypalBundle] Le statut ' . $request->get('payment_status') .
                                        ' n\'est pas correct');
            return false;
        }

        // Vérification du champs custom
        if (!$request->get('custom')) {
            $this->get('logger')->error("[PaypalBundle] Aucun référence de paiement reçue dans la notification Paypal.\n" .
                                        'Données reçues: ' . $request->getQueryString());
            return false;
        }
        if ($request->get('custom') != $payment->getRef()) {
            $this->get('logger')->error('[PaypalBundle] La référence de Paypal ' . $request->get('custom') .
                                        ' ne correspond pas à la référence du paiement ' . $payment->getRef());
            return false;
        }

        return true;
    }

    /**
     * Vérifie que les données reçues viennent bien de Paypal
     * @return bool
     */
    private function checkPaypal()
    {
        $this->get('logger')->debug('[PaypalBundle] ' . __CLASS__ . '::' . __FUNCTION__);

        // Appel de Paypal pour vérifier que les informations reçues sont correctes
        $request = 'cmd=_notify-validate';
        foreach ($_REQUEST as $key => $value) {
            $request .= '&' . $key . '=' . urlencode($value);
        }
        $host = ($this->container->getParameter('paypal.debug') ? 'www.sandbox.paypal.com' : 'www.paypal.com');

        if (!($fp = fsockopen ($host, 80, $errno, $errstr, 30))) {
            $this->get('logger')->error('[PaypalBundle] Erreur de connexion à Paypal nº' . $errno . ': ' . $errstr);
            return false;
        }
        $this->get('logger')->debug('[PaypalBundle] Connexion à Paypal OK');
        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n" .
                  "host: $host\r\n" .
                  "Content-Type: application/x-www-form-urlencoded\r\n" .
                  "Content-Length: " . strlen($request) . "\r\n\r\n";
        $this->get('logger')->debug('[PaypalBundle] Envoi de la requête ' . $request);
        fputs($fp, $header . $request);
        while (!feof($fp)) {
            $result = fgets($fp, 1024);
            $this->get('logger')->debug('[PaypalBundle] ' . $result);
            switch (trim($result)) {
                case 'VERIFIED':
                    return true;
                case 'INVALID':
                    $this->get('logger')->error('[PaypalBundle] Erreur de vérification par Paypal pour la requête ' .
                                                $request);
                    return false;
            }
        }
        $this->get('logger')->error('[PaypalBundle] Impossible de vérifier auprès de Paypal la requête ' . $request);
        return false;
    }
}
