<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\User;
use AppBundle\Form\User\UserType;

class UserController extends Controller
{
  /**
  * Enable or disable an user.
  *
  * @param  string  $id      User id
  * @param  bool $enabled    True to enable, false to disable
  * @return JsonResponse     Json response with new user status
  *
  * @Route("/user/enable/{id}/{enabled}",
  *   requirements = {"id" = "\d+","enabled" = "[0-1]+"},
  *   name="user_enable",
  *   options = {"expose" = true}
  * )
  */
  public function userEnableAction($id, $enabled)
  {
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository('AppBundle:User');
    $user = $repository->find($id);

    $user->setEnabled($enabled);
    $manager->flush();

    return new JsonResponse(array('enabled' => $enabled));
  }

  /**
  * Promote or demote user with admin role
  *
  * @param  string  $id      User id
  * @param  bool $promote    True to promote, false to demote
  * @return JsonResponse     Json response with new user role
  *
  * @Route("/user/promote-admin/{id}/{promote}",
  *   requirements = {"id" = "\d+","promote" = "[0-1]+"},
  *   name="user_promote_admin",
  *   options = {"expose" = true}
  * )
  */
  public function userPromoteAdminAction($id, $promote)
  {

    $userManager = $this->get('fos_user.user_manager');
    $user = $userManager->findUserBy(array('id'=>$id));

    if($promote == 1){
       $user->addRole('ROLE_ADMIN');
    } else {
       $user->removeRole('ROLE_ADMIN');
    }

    $userManager->updateUser($user);

    return new JsonResponse(array('promote' => $promote));
  }

  //  /**
  //   * @Route("promote-all", name="promote-all" )
  //   **/
  //   public function promoteAllAction(){
  //
  //     $userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
  //     $arr_users = $userRepo->findAll();
  //
  //
  //     $userManager = $this->get('fos_user.user_manager');
  //
  //     foreach($arr_users as $user){
  //       $user->addRole('ROLE_NDF');
  //       $userManager->updateUser($user);
  //     }
  //
  //
  //     return new Response();
  // }


  /**
   * Add an user to the logged user's company
   * @return Response Rendered view
   *
   * @Route("admin/utilisateurs/ajouter",
   *   name="admin_utilisateurs_ajouter"
   * )
   */
  public function utilisateursAjouterAction()
  {
    $userManager = $this->get('fos_user.user_manager');
    $user = $userManager->createUser();

    $form = $this->createForm(new UserType(), $user);

    $request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$user->setCompany( $this->getUser()->getCompany() );

      if($form['admin']->getData() == 'true'){
        $user->addRole('ROLE_ADMIN');
      }

      foreach($form['permissions']->getData() as  $role){
          $user->addRole($role);
      }

      $tokenGenerator = $this->get('fos_user.util.token_generator');
      $password = substr($tokenGenerator->generateToken(), 0, 8); // 8 chars
      $user->setPlainPassword($password);

      $userManager->updateUser($user);

      //envoi d'un email en interne
      $message = $this->renderView('admin/utilisateurs/admin_utilisateurs_welcome_email.html.twig', array(
        'user' => $user,
        'password' => $password,
      ));
      $mail = \Swift_Message::newInstance()
        ->setSubject('Bienvenue sur J\'aime gérer '.$user->getFirstName().' !')
        ->setFrom('laura@jaime-gerer.com')
        ->setTo($user->getEmail())
        ->setBody($message, 'text/html');
      $this->get('mailer')->send($mail);

			return $this->redirect($this->generateUrl(
					'admin_utilisateurs_liste'
			));
		}

    return $this->render('admin/utilisateurs/admin_utilisateurs_ajouter.html.twig', array(
      'form' => $form->createView(),
      'company' => $this->getUser()->getCompany()
    ));
  }

  /**
   * @Route("/wp-register", name="register_from_wordpress")
   */
  public function registerFromWordpressAction()
  {
    //autoriser le cross domain
    if(isset($_SERVER['HTTP_ORIGIN'])){
      switch ($_SERVER['HTTP_ORIGIN']) {
        case 'https://www.jaime-gerer.com':
          header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
          header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          header('Access-Control-Max-Age: 1000');
          header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
          break;
      }
    }
    $userManager = $this->get('fos_user.user_manager');

    $request = $this->getRequest();
    $posts = $request->request->all();

    //vérifier si l'utilisateur existe déjà
    $existingUser = $userManager->findUserByEmail($posts['email']);
    if($existingUser != null){
      throw new \Exception('This user already exists.');
    }

    $firstname = $posts['firstname'];
    $lastname = $posts['lastname'];
    $email = $posts['email'];
    $plainPassword = $posts['plainPassword'];
    $company = $posts['company'];
    $phone = $posts['phone'];

    //création du nouvel utilisateur
    $user = $userManager->createUser();
    $user->setFirstname($firstname);
    $user->setLastname($lastname);
    $user->setEmail($email);
    $user->setPlainPassword($plainPassword);
    $user->setUsername($email);;
    $user->setPhone($phone);;
    $user->setEnabled(false);

    $user->addRole('ROLE_ADMIN');
    $user->addRole('ROLE_COMMERCIAL');
    $user->addRole('ROLE_COMPTA');
    $user->addRole('ROLE_COMMUNICATION');
    $user->addRole('ROLE_RH');

    //génération du token de confirmation et envoi du mail d'activation au nouvel utilisateur
    $tokenGenerator = $this->get('fos_user.util.token_generator');
    $user->setConfirmationToken($tokenGenerator->generateToken());
    $userManager->updateUser($user);
    $this->get('fos_user.mailer')->sendConfirmationEmailMessage($user);

    //envoi d'un email en interne
    $message=$firstname.' '.$lastname.' de l\'organisation '.$company.' : '.$email.' - '.$phone;
    $mail = \Swift_Message::newInstance()
      ->setSubject('Youpi, un nouvel utilisateur s\'est inscrit sur J\'aime gérer ! ')
      ->setFrom('laura@jaime-gerer.com')
      ->setTo('salavin@nicomak.eu')
      ->setCc('laura@jaime-gerer.com')
      ->setBody($message, 'text/html');
    $this->get('mailer')->send($mail);

    $response = new Response();
    $response->setStatusCode(Response::HTTP_OK);

    return $response;
  }

}
