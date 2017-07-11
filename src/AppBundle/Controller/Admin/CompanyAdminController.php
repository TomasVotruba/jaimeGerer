<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Form\CompanyType;

use AppBundle\Entity\Company;


class CompanyAdminController extends Controller
{

  /**
   * @Route("/admin/company", name="admin_company_edit")
   */
  public function companyEditerAction()
  {
    $em = $this->getDoctrine()->getManager();

    if($this->getUser() != null){

      if($this->getUser()->getCompany() != null){
        $company = $this->getUser()->getCompany();
      } else {
        $company = new Company();
      }

      $form = $this->createForm(new CompanyType(), $company);

      $request = $this->getRequest();
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $init = false;
        if($company->getId() == null){
          $init = true;
        }

        $em->persist($company);

        $user= $this->getUser();
        $user->setCompany($company);
        $em->persist($user);

        $em->flush();


        $this->get('session')->getFlashBag()->add(
            'success',
            'Les modifications ont bien été enregistrées.'
        );

        if($init == true){
          return $this->redirect($this->generateUrl('homepage'));
        }

      }

      return $this->render('company/company_editer.html.twig', array(
          'form' => $form->createView(),
          'company' => $company
      ));

    } else {
      return $this->redirect('login');
    }
  }

  /**
   * @Route("/admin/company/upload/tampon", name="admin_company_upload_tampon")
   */
  public function companyUploadTamponAction()
  {
    $company = $this->getUser()->getCompany();

    $em = $this->getDoctrine()->getManager();
    $requestData = $this->getRequest();

    $arr_files = $requestData->files->all();
    $file = $arr_files["files"][0];

    //enregistrement temporaire du fichier uploadé
    $filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
    $path =  $this->get('kernel')->getRootDir().'/../web/upload/tampon/';
    $file->move($path, $filename);

    $oldTampon = null;
    if($company->getTampon() != null){
      $oldTampon = $company->getTampon();
    }

    $company->setTampon($filename);
    $em->persist($company);
    $em->flush();

    if($oldTampon){
      unlink($path.$oldTampon);
    }

    $response = new JsonResponse();
    $response->setData(array(
        'filename' => $filename
    ));

    return $response;
  }

    /**
   * @Route("/admin/company/upload/logo", name="admin_company_upload_logo")
   */
  public function companyUploadLogoAction()
  {
    $company = $this->getUser()->getCompany();

    $em = $this->getDoctrine()->getManager();
    $requestData = $this->getRequest();

    $arr_files = $requestData->files->all();
    $file = $arr_files["files"][0];

    //enregistrement temporaire du fichier uploadé
    $filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
    $path =  $this->get('kernel')->getRootDir().'/../web/upload/logo/';
    $file->move($path, $filename);

    $oldLogo = null;
    if($company->getLogo() != null){
      $oldLogo = $company->getLogo();
    }

    $company->setLogo($filename);
    $em->persist($company);
    $em->flush();

    if($oldLogo) {
      unlink($path.$oldLogo);
    }

    $response = new JsonResponse();
    $response->setData(array(
        'filename' => $filename
    ));

    return $response;
  }

  /**
   * Display the list of users in the logged user's company
   * @return Response Rendered view
   *
   * @Route("admin/utilisateurs/liste",
   *   name="admin_utilisateurs_liste"
   * )
   */
  public function utilisateursListeAction(){
    $company = $this->getUser()->getCompany();

    $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
    $arr_users = $userRepo->findByCompany($company);

    return $this->render('admin/utilisateurs/admin_utilisateurs_liste.html.twig', array(
      'arr_users' => $arr_users,
      'company' => $company
    ));
  }


}
