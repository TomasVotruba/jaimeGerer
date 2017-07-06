<?php

namespace AppBundle\Controller\Emailing;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Swift_Attachment;
use Mailgun\Mailgun;
use cspoo_swiftmailer_mailgun;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
//~ use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use RC\AmChartsBundle\AmCharts\AmPieChart;


use AppBundle\Entity\Emailing\Campagne;
use AppBundle\Entity\CRM\Rapport;
use AppBundle\Entity\CRM\RapportFilter;
use AppBundle\Form\Emailing\CampagneType;
use AppBundle\Form\Emailing\RapportListeContactType;
use AppBundle\Form\CRM\RapportType;
use AppBundle\Form\CRM\RapportFilterType;

class CampagneController extends Controller
{

	/**
	 * @Route("/emailing", name="emailing_index")
	 */ 
	public function indexAction()
	{
		return $this->render('emailing/emailing_index.html.twig');
		

	}
	
	/**
	 * @Route("/emailing/campagne/liste", name="emailing_campagne_liste")
	 */
	public function campagneListeAction()
	{ 
		return $this->render('emailing/campagne/emailing_campagne_liste.html.twig'); 
	}
	
	/**
	 * @Route("/emailing/campagne/liste/ajax", name="emailing_campagne_liste_ajax")
	 */
	public function campagneListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
	
		$col = $arr_sort[0]['column'];
	
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Emailing\Campagne'); 
	
		$arr_search = $requestData->get('search');
	
		$list = $repository->findForList(
				$this->getUser(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);

		foreach( $list as $k=>$v )
		{
			$ListesContact = '';
			$campagne = $repository->find($v['id']);
			foreach( $campagne->getListesContact() as $rapport )
			{
				$ListesContact .= '<a href="'.$this->generateUrl('emailing_rapport_voir', array('id'=>$rapport->getId())).'">'.$rapport->getNom().'</a><br />';
			}
			$list[$k]['listeContact'] = $ListesContact;
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()),
				'recordsFiltered' => $repository->countForList($this->getUser(), $arr_search['value']),
				'aaData' => $list,
		));
	
		return $response;
	}
	 
	/**
	 * @Route("/emailing/statistiques", name="emailing_campagne_statistiques")
	 */
	public function campagneStatistiquesAction()
	{ 
		return $this->render('emailing/campagne/emailing_campagne_statistiques.html.twig');
	}
	
	/**
	 * @Route("/emailing/statistiques/{id}", name="emailing_campagne_stat")
	 */
	public function campagneStatAction(campagne $campagne)
	{ 
		$key = $this->container->getParameterBag()->get('mailgun.key');
		$domain = $this->container->getParameterBag()->get('mailgun.domain');
		$mgClient = new Mailgun($key);
		$result = $mgClient->get("$domain/campaigns/".$campagne->getId());
		$delivered = $result->http_response_body->delivered_count;
		$clicked = $result->http_response_body->clicked_count;
		$opened = $result->http_response_body->opened_count;
		//~ $queryString = array('event' => 'clicked');
		$bounced = array('temporary' => 0, 'permanent' => 0);
		//~ $queryString = array(
							//~ 'temporary' => array('event' => 'rejected OR failed', 'severity' => 'temporary'), 
							//~ 'permanent' => array('event' => 'rejected OR failed', 'severity' => 'permanent')
							//~ );
		$queryString = array(
							'temporary' => array('event' => 'rejected OR failed', 'severity' => 'temporary'), 
							'permanent' => array('event' => 'bounced', 'code' => 554),
							);
		foreach( $queryString as $k=>$v )
		{
			$result = $mgClient->get("$domain/campaigns/".$campagne->getId()."/events", $v);
			if( isset($result->http_response_body->items) ) $bounced[$k] = count($result->http_response_body->items);
			elseif( isset($result->http_response_body) ) $bounced[$k] = count($result->http_response_body);
			//~ if( isset($result->http_response_body->items) ) $bounced[$k] = count($result->http_response_body->items);
		}
		$queryString = array('groupby' => 'day');

		$stats = $mgClient->get("$domain/campaigns/".$campagne->getId()."/stats")->http_response_body;
		$events = array('opens', 'clicks');
		$output = array();

		$maxDate = 0;
		//~ exit;
		foreach( $events as $k=>$v )
		{

			$result = $mgClient->get("$domain/campaigns/".$campagne->getId()."/$v", $queryString);
			foreach( $result->http_response_body as $key=>$value )
			{
				$date = \DateTime::createFromFormat('D, d M Y H:i:s e+' , $value->day)->format('Y-m-d');
				$output[$date]['date'] = $date;
				$output[$date][$v] = $value->total;
			}
			$newMaxDate = \DateTime::createFromFormat('D, d M Y H:i:s e+' , $value->day)->format('U');
			if( $maxDate < $newMaxDate ) $maxDate = $newMaxDate;
	        //~ var_dump($newMaxDate); exit;
		}
        $endDate = new \DateTime('@'.$maxDate);
        $startDate = $campagne->getDateEnvoi()->setTime(0,0);
        $finalOutput = array();
		while( $startDate <= $endDate )
		{
			$finalOutput[$startDate->format('Y-m-d')] = isset($output[$startDate->format('Y-m-d')]) ? $output[$startDate->format('Y-m-d')] : array();

			if( !isset($finalOutput[$startDate->format('Y-m-d')]['date']) ) $finalOutput[$startDate->format('Y-m-d')]['date'] = $startDate->format('Y-m-d');
			if( !isset($finalOutput[$startDate->format('Y-m-d')]['opens']) ) $finalOutput[$startDate->format('Y-m-d')]['opens'] = 0;
			if( !isset($finalOutput[$startDate->format('Y-m-d')]['clicks']) ) $finalOutput[$startDate->format('Y-m-d')]['clicks'] = 0;
			$startDate->add(new \DateInterval('P1D'));
		}
		unset($output);

        $lineChart = new \RC\AmChartsBundle\AmCharts\AmLineChart();
        
        $lineChart->renderTo('piechart');
        $lineChart->setCategoryAxis(array("minPeriod" => "DD", "parseDates" => true, "minorGridAlpha" => 0.1, "minorGridEnabled" => true));
        $lineChart->addValueAxis(array("integersOnly" => true));
        // courbe du taux d'ouverture
        $lineChart->addGraph(array("id" => "g1", 
										  "balloonText" => "[[title]] - [[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
										  "bullet" => "round",
										  "bulletSize" => 8,
										  "lineColor" => "#2B4EEC",
											"lineThickness"  => 2,
											"type" => "smoothedLine",
											"title" => "Taux d’ouverture",
											"valueField" => "opens"	));
        // courbe du taux de click
        $lineChart->addGraph(array("id" => "g2", 
										  "balloonText" => "[[title]] - [[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
										  "bullet" => "round",
										  "bulletSize" => 8,
										  "lineColor" => "#FCD202",
											"lineThickness"  => 2,
											"type" => "smoothedLine",
											"title"=> "Taux de click",
											"valueField" => "clicks"	));
        $lineChart->setTheme('light');
        $lineChart->setCategoryField('date');

		foreach( $finalOutput as $k=>$v )
		{
			$lineChart->addData($v);
		}
  
  
		$lineChart->jsonSettings->setProperty( 'language', 'fr');
		$lineChart->jsonSettings->setProperty( 'legend', (object) array("position" => "right",
																		//~ "marginLeft" => 20,
																		//~ "autoMargins" => true,
																		"labelText" => "[[title]] ",
																		"periodValueText" => ": [[value.sum]]",
																		//~ "equalWidths" => false,
																		//~ "valueText" => " $[[value]] [[percents]]%",
																		//~ "valueText" => "",
																		//~ "valueText" => false,
																		//~ "enabled" => false,
																		//~ "data" => array(array("title" => "One", "color" => "#3366CC")),
															));
		$lineChart->jsonSettings->setProperty( 'chartCursor',(object) array(
																"fullWidth" => true,
																"valueLineEabled" => true,
																"valueLineBalloonEnabled" => true,
																"valueLineAlpha" => 0.5,
																"cursorAlpha" => 0
															));


		return $this->render('emailing/campagne/emailing_campagne_stat.html.twig', array(
			'campagne' => $campagne,
			'bounced' => $bounced,
			'stats' => $stats,
            'chart' => $lineChart
		));
		//~ return $this->render('emailing/campagne/emailing_campagne_stat.html.twig');
	}
	
	/**
	 * @Route("/emailing/campagne/liste/ajax/stats", name="emailing_campagne_liste_ajax_stats")
	 */
	public function campagneListeAjaxStatsAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
	
		$col = $arr_sort[0]['column'];
	
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Emailing\Campagne'); 
	
		$arr_search = $requestData->get('search');
	
		$list = $repository->findForListStats(
				$this->getUser(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);

		foreach( $list as $k=>$v )
		{
			$ListesContact = '';
			$campagne = $repository->find($v['id']);
			foreach( $campagne->getListesContact() as $rapport )
			{
				$ListesContact .= '<a href="'.$this->generateUrl('emailing_rapport_voir', array('id'=>$rapport->getId())).'">'.$rapport->getNom().'</a><br />';
			}
			$list[$k]['listeContact'] = $ListesContact;
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()),
				'recordsFiltered' => $repository->countForListStats($this->getUser(), $arr_search['value']),
				'aaData' => $list,
		));
	
		return $response;
	}
	 
	/**
	 * @Route("/emailing/campagne/ajouter", name="emailing_campagne_ajouter")
	 */
	public function campagneAjouterAction()
	{
		$campagne = new Campagne();
		$campagne->setUserCreation($this->getUser());
		$form = $this->createForm(
					new CampagneType(
							$compte->getUserGestion()->getId(), 
							$this->getUser()->getCompany()->getId()
					), 
					$campagne
				);
		$request = $this->getRequest();
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$campagne->setDateCreation(new \DateTime(date('Y-m-d')));
			$campagne->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
								
			$em->persist($campagne);
			$em->flush();

			$key = $this->container->getParameterBag()->get('mailgun.key');
			$domain = $this->container->getParameterBag()->get('mailgun.domain');
			$mgClient = new Mailgun($key);
			$result = $mgClient->post("$domain/campaigns", array(
				'name' => $campagne->getNomCampagne(),
				'id'   => $campagne->getId()
			));
			return $this->redirect($this->generateUrl(
					'emailing_campagne_template',
					array('id' => $campagne->getId())
			));
		}
		
		return $this->render('emailing/campagne/emailing_campagne_ajouter.html.twig', array(
			'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/emailing/campagne/template/{id}", name="emailing_campagne_template")
	 */
	public function campagneTemplateAction(Campagne $campagne)
	{
		$form = $this->createForm(
							new CampagneType(
							$compte->getUserGestion()->getId(), 
							$this->getUser()->getCompany()->getId()
					), 
					$campagne
		)
		->remove('nomCampagne')
		->remove('nomExpediteur')
		->remove('objetEmail')
		->remove('emailExpediteur')
		->add('template', 'textarea', array(
        		'required' => false,
            	'label' => 'Template',
				'attr' => array(
					'class'      => 'tinymce',
					'data-theme' => 'advanced'
				)
        	));
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) { 
			$data = $form->getData();
			$em = $this->getDoctrine()->getManager();
			$em->persist($campagne);
			$em->flush();
			return $this->redirect($this->generateUrl(
					'emailing_campagne_contact',
					array('id' => $campagne->getId())
			));
		}
	
		return $this->render('emailing/campagne/emailing_campagne_template.html.twig', array(
			'campagne' => $campagne,
			'form' => $form->createView(),
			'hide_tiny' => true
		));
	}
	
	/**
	 * @Route("/emailing/campagne/contact/{id}", name="emailing_campagne_contact")
	 */
	public function campagneContactAction(Campagne $campagne)
	{
		$form = $this->createForm(
							new CampagneType(
							$compte->getUserGestion()->getId(), 
							$this->getUser()->getCompany()->getId()
					), 
					$campagne
		)
		->remove('nomCampagne')
		->remove('nomExpediteur')
		->remove('objetEmail')
		->remove('emailExpediteur')
             ->add('listesContact', 'entity', array(
            		'class'=>'AppBundle:CRM\Rapport',
            		'property' => 'nom',
            		'query_builder' => function (\AppBundle\Entity\CRM\RapportRepository $er) {
            			return $er->createQueryBuilder('s')
            			->where('s.module = :module')
            			->andWhere('s.type = :type')
            			->setParameter('module', 'Emailing')
            			->setParameter('type', 'contact');
            		},
            		'required' => false,
             		'multiple' => true,
            		'label' => 'Liste de contact'
            ))
			->add('nouvelleListe', 'collection', array(
					'type' => new RapportListeContactType(),
					'allow_add' => true,
					'allow_delete' => true,
					'by_reference' => false,
					'label_attr' => array('class' => 'hidden'),
					'mapped' => false,
					'label' => 'Nouvelle liste'
			));
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) { 
			$em = $this->getDoctrine()->getManager();
			$dataAll = $request->request->all();

			$data = $form->getData();

			$arr_listes = $form->get('nouvelleListe')->getData();
			$champs = $em->getClassMetadata('AppBundle:CRM\RapportFilter')->getFieldNames();
			foreach($arr_listes as $k=>$liste){

				$liste->setModule('Emailing');
				$liste->setType('Contact');
				$liste->setDateCreation(new \DateTime(date('Y-m-d')));
				$liste->setUserCreation($this->getUser());
				$liste->setCompany($this->getUser()->getCompany());
				$em->persist($liste);
				$em->flush();
				foreach( $dataAll[$form->getName()]['nouvelleListe'][$k]['filtres'] as $key=>$value )
				{
					$NewFilter = new RapportFilter();
					$NewFilter->setRapport($liste);
					foreach( $value as $champ=>$valeur )
					{
						if( in_array($champ, $champs) )
						{
							$methodSet = 'set'.ucfirst($champ);
							eval('$NewFilter->$methodSet($valeur);');
						}
					}
					$em->persist($NewFilter);
					$em->flush();
				}
				$campagne->addListeContact($liste);
			}
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'emailing_campagne_envoi',
					array('id' => $campagne->getId())
			));
		}
	

		return $this->render('emailing/campagne/emailing_campagne_contact.html.twig', array(
			'campagne' => $campagne,
			'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/emailing/campagne/envoi/{id}", name="emailing_campagne_envoi")
	 */
	public function campagneEnvoiAction(Campagne $campagne)
	{
		$form = $this->createForm(
							new CampagneType(
							$compte->getUserGestion()->getId(), 
							$this->getUser()->getCompany()->getId()
					), 
					$campagne
		)
		->remove('nomCampagne')
		->remove('nomExpediteur')
		->remove('objetEmail')
		->remove('emailExpediteur')
		->add('dateEnvoi', 'datetime', array('widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy hh:mm',
			'required' => false,
			'label' => 'Envoyer la campagne le'
		))
		->add('now', 'hidden', array(
				'required' => false,
				'data'	=> 0,
				'mapped' => false
		))
		->add('envoyerMnt', 'submit', array(
			'attr' => array('class' => 'btn btn-success'),
			'label' => 'Envoyer la campagne maintenant'
		));
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) { 
			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			//~ var_dump($data->getDateEnvoi()); exit;
			//~ $dateEnvoi = $form->get('now')->getData() ? \DateTime::createFromFormat('U', time()) : \DateTime::createFromFormat('d/m/Y H:i', $data->getDateEnvoi());
//~ 
			//~ $campagne->setDateEnvoi($dateEnvoi);
			$em->persist($campagne);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'emailing_campagne_voir',
					array('id' => $campagne->getId())
			));
		}
	
		return $this->render('emailing/campagne/emailing_campagne_envoi.html.twig', array(
			'campagne' => $campagne,
			'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/emailing/campagne/voir/{id}", name="emailing_campagne_voir")
	 */
	public function campagneVoirAction(Campagne $campagne)
	{
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		if( $campagne->getEnvoyee() )
		{
			$key = $this->container->getParameterBag()->get('mailgun.key');
			$domain = $this->container->getParameterBag()->get('mailgun.domain');
			//~ $domain = "sandboxd55b4a445a5948409dc77ea13bef9d5a.mailgun.org";
			$mgClient = new Mailgun($key);
			$result = $mgClient->get("$domain/campaigns/".$campagne->getId());
			$delivered = $result->http_response_body->delivered_count;
			$clicked = $result->http_response_body->clicked_count;
			$opened = $result->http_response_body->opened_count;
			//~ $queryString = array('event' => 'clicked');
			$bounced = array('temporary' => 0, 'permanent' => 0);
			//~ $queryString = array(
								//~ 'temporary' => array('event' => 'rejected OR failed', 'severity' => 'temporary'), 
								//~ 'permanent' => array('event' => 'rejected OR failed', 'severity' => 'permanent')
								//~ );
			$queryString = array(
								'temporary' => array('event' => 'rejected OR failed', 'severity' => 'temporary'), 
								'permanent' => array('event' => 'bounced', 'code' => 554),
								);
			foreach( $queryString as $k=>$v )
			{
				$result = $mgClient->get("$domain/campaigns/".$campagne->getId()."/events", $v);
				//~ var_dump($result); exit;
				if( isset($result->http_response_body->items) ) $bounced[$k] = count($result->http_response_body->items);
				elseif( isset($result->http_response_body) ) $bounced[$k] = count($result->http_response_body);
			}
			$queryString = array('groupby' => 'day');

			$stats = $mgClient->get("$domain/campaigns/".$campagne->getId()."/stats")->http_response_body;
			$events = array('opens', 'clicks');
			$output = array();

			$maxDate = 0;
			//~ exit;
			foreach( $events as $k=>$v )
			{

				$result = $mgClient->get("$domain/campaigns/".$campagne->getId()."/$v", $queryString);
				foreach( $result->http_response_body as $key=>$value )
				{
					$date = \DateTime::createFromFormat('D, d M Y H:i:s e+' , $value->day)->format('Y-m-d');
					$output[$date]['date'] = $date;
					$output[$date][$v] = $value->total;
				}
				$newMaxDate = isset($value) ? \DateTime::createFromFormat('D, d M Y H:i:s e+' , $value->day)->format('U') : time();
				if( $maxDate < $newMaxDate ) $maxDate = $newMaxDate;
				//~ var_dump($newMaxDate); exit;
			}
			$endDate = new \DateTime('@'.$maxDate);
			$abc = new \DateTime($campagne->getDateEnvoi()->format('Y-m-d H:i:s'));
		//~ var_dump($campagne->getDateEnvoi()); 
			$startDate = $abc->setTime(0,0);
		//~ var_dump($campagne->getDateEnvoi()); exit;
			$finalOutput = array();
			while( $startDate <= $endDate )
			{
				$finalOutput[$startDate->format('Y-m-d')] = isset($output[$startDate->format('Y-m-d')]) ? $output[$startDate->format('Y-m-d')] : array();

				if( !isset($finalOutput[$startDate->format('Y-m-d')]['date']) ) $finalOutput[$startDate->format('Y-m-d')]['date'] = $startDate->format('Y-m-d');
				if( !isset($finalOutput[$startDate->format('Y-m-d')]['opens']) ) $finalOutput[$startDate->format('Y-m-d')]['opens'] = 0;
				if( !isset($finalOutput[$startDate->format('Y-m-d')]['clicks']) ) $finalOutput[$startDate->format('Y-m-d')]['clicks'] = 0;
				$startDate->add(new \DateInterval('P1D'));
			}
			unset($output);

			$lineChart = new \RC\AmChartsBundle\AmCharts\AmLineChart();
			
			$lineChart->renderTo('piechart');
			$lineChart->setCategoryAxis(array("minPeriod" => "DD", "parseDates" => true, "minorGridAlpha" => 0.1, "minorGridEnabled" => true));
			$lineChart->addValueAxis(array("integersOnly" => true));
			// courbe du taux d'ouverture
			$lineChart->addGraph(array("id" => "g1", 
											  "balloonText" => "[[title]] - [[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
											  "bullet" => "round",
											  "bulletSize" => 8,
											  "lineColor" => "#2B4EEC",
												"lineThickness"  => 2,
												"type" => "smoothedLine",
												"title" => "Taux d’ouverture",
												"valueField" => "opens"	));
			// courbe du taux de click
			$lineChart->addGraph(array("id" => "g2", 
											  "balloonText" => "[[title]] - [[category]]<br><b><span style='font-size:14px;'>[[value]]</span></b>",
											  "bullet" => "round",
											  "bulletSize" => 8,
											  "lineColor" => "#FCD202",
												"lineThickness"  => 2,
												"type" => "smoothedLine",
												"title"=> "Taux de click",
												"valueField" => "clicks"	));
			$lineChart->setTheme('light');
			$lineChart->setCategoryField('date');

			foreach( $finalOutput as $k=>$v )
			{
				$lineChart->addData($v);
			}
	  
	  
			$lineChart->jsonSettings->setProperty( 'language', 'fr');
			$lineChart->jsonSettings->setProperty( 'legend', (object) array("position" => "right",
																			//~ "marginLeft" => 20,
																			//~ "autoMargins" => true,
																			"labelText" => "[[title]] ",
																			"periodValueText" => ": [[value.sum]]",
																			//~ "equalWidths" => false,
																			//~ "valueText" => " $[[value]] [[percents]]%",
																			//~ "valueText" => "",
																			//~ "valueText" => false,
																			//~ "enabled" => false,
																			//~ "data" => array(array("title" => "One", "color" => "#3366CC")),
																));
			$lineChart->jsonSettings->setProperty( 'chartCursor',(object) array(
																	"fullWidth" => true,
																	"valueLineEabled" => true,
																	"valueLineBalloonEnabled" => true,
																	"valueLineAlpha" => 0.5,
																	"cursorAlpha" => 0
																));
		}
		else
		{
			$bounced = false;
			$stats = false;
			$lineChart = false;
		}


		return $this->render('emailing/campagne/emailing_campagne_voir.html.twig', array(
			'campagne' => $campagne,
			'bounced' => $bounced,
			'stats' => $stats,
            'chart' => $lineChart
		));
	}
	
	/**
	 * @Route("/emailing/campagne/editer/{id}", name="emailing_campagne_editer")
	 */
	public function campagneEditerAction(Campagne $campagne)
	{
		$form = $this->createForm(
				new CampagneType(
						$compte->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId()
					), $campagne
				);
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) { 
	
			$campagne->setDateEdition(new \DateTime(date('Y-m-d')));
			$campagne->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($campagne);
			$em->flush();
	
			$key = $this->container->getParameterBag()->get('mailgun.key');
			$domain = $this->container->getParameterBag()->get('mailgun.domain');
			$mgClient = new Mailgun($key);
			try{
				$result = $mgClient->get("$domain/campaigns/".$campagne->getId());
				$result = $mgClient->put("$domain/campaigns/".$campagne->getId(), array(
					'name' => $campagne->getNomCampagne(),
					'id'   => $campagne->getId()
				));
			} catch(\Exception $e){
				$result = $mgClient->post("$domain/campaigns", array(
					'name' => $campagne->getNomCampagne(),
					'id'   => $campagne->getId()
				));
			}
			return $this->redirect($this->generateUrl(
					'emailing_campagne_template',
					array('id' => $campagne->getId())
			));
		}
	
		return $this->render('emailing/campagne/emailing_campagne_editer.html.twig', array(
				'form' => $form->createView(),
				'campagne' => $campagne
		));
	}
	
	/**
	 * @Route("/emailing/campagne/supprimer/{id}", name="emailing_campagne_supprimer")
	 */
	public function campagneSupprimerAction(Campagne $campagne)
	{
		$form = $this->createFormBuilder()->getForm();
		
		$request = $this->getRequest();
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($campagne);
			$em->flush();

			$key = $this->container->getParameterBag()->get('mailgun.key');
			$domain = $this->container->getParameterBag()->get('mailgun.domain');
			$mgClient = new Mailgun($key);
			try{
				$result = $mgClient->get("$domain/campaigns/".$campagne->getId());
				$result = $mgClient->delete("$domain/campaigns/".$campagne->getId());
			} catch(\Exception $e){

			}
		
			return $this->redirect($this->generateUrl(
				'emailing_campagne_liste'
			));
		}
		
		return $this->render('emailing/campagne/emailing_campagne_supprimer.html.twig', array(
				'form' => $form->createView(),
				'campagne' => $campagne
		));
	}
	
	/**
	 * @Route("/emailing/campagne/lancer", name="emailing_campagne_lancer")
	 */
	public function campagneLancerAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Emailing\Campagne'); 
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$campagnes = $repository->findBy(array('dateEnvoi' => new \DateTime('now')));
		//~ var_dump($campagnes); exit;


		$key = $this->container->getParameterBag()->get('mailgun.key');
		$domain = $this->container->getParameterBag()->get('mailgun.domain');
		$mgClient = new Mailgun($key);
		
		// Destinataires de test
		$TestTo = array('hichem <mohamed.hichem@gmail.com>',
						'hich <hichem_ben_messaoud@yahoo.com>',
						'Laura web4change <laura@web4change.com>',
						'Laura nicomak <gilquin@nicomak.eu>',
						'Laura j\'aime gérer <laura@jaime-gerer.com>',
						'Pierre Lebourg <pierre@web4change.com>'
						);

		foreach( $campagnes as $campagne )
		{
			foreach( $campagne->getListesContact() as $rapport )
			{
				//~ var_dump($rapport);
				$arr_filters = $filterRepo->findByRapport($rapport);
				$arr_obj = $objRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany());
				$i = 0;
				foreach( $arr_obj as $contact )
				{
					// Résolution des emails
					$to[] = $contact." <".$contact->getEmail().">";
					// Pour ne pas spammer les emails de tests
					break;
					// Envoi par palier de 1000 destinataires
					if( count($to) == 1000 )
					{
						// switch de $to pour les tests
						$to = $TestTo;
						//~ var_dump($to); echo 1; exit;
						$result = $mgClient->sendMessage($domain, array(
									//~ 'from'    => 'mohamed hichem <mohamed.hichem@gmail.com>',
									'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
									'to'      => implode(',', $to),
									'subject' => $campagne->getObjetEmail(),
									'html'    => $campagne->getTemplate(),
									'o:campaign' => $campagne->getId(),
								));
						$to = array();
					}
				}
				if( count($to) > 0 )
				{
					$to = $TestTo;
					$result = $mgClient->sendMessage($domain, array(
								//~ 'from'    => 'mohamed hichem <mohamed.hichem@gmail.com>',
								'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
								'to'      => implode(',', $to),
								'subject' => $campagne->getObjetEmail(),
								'html'    => $campagne->getTemplate(),
								'o:campaign' => $campagne->getId(),
								'recipient-variables' => '{"mohamed.hichem@gmail.com": {"first":"hich", "id":1},
							   "hichem_ben_messaoud@yahoo.com": {"first":"ben", "id": 2},
							   "laura@web4change.com": {"first":"web4change", "id": 3},
							   "gilquin@nicomak.eu": {"first":"nicomak", "id": 4},
							   "laura@jaime-gerer.com": {"first":"jaimegerer", "id": 5}}'
							));
				}
				// Pour ne pas spammer les emails de tests
				break;
			}
			$campagne->setEnvoyee(true);
			$em->persist($campagne);
			$em->flush();
		}
		echo 1;
	}
	
	/**
	 * @Route("/emailing/campagne/tds/{id}", name="emailing_campagne_tds")
	 */
	public function campagneTdsAction(Campagne $campagne)
	{
		//~ echo "hich"; exit;
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$key = $this->container->getParameterBag()->get('mailgun.key');
		$domain = $this->container->getParameterBag()->get('mailgun.domain');
		$mgClient = new Mailgun($key);
		
		// Destinataires de test
		$TestTo = array('hichem <mohamed.hichem@gmail.com>',
						'hich <hichem_ben_messaoud@yahoo.com>',
						'qsdfqs <sdfgs12dfghdthebvsrfdf@yahoo.com>',
						'Laura web4change <laura@web4change.com>',
						'Laura nicomak <gilquin@nicomak.eu>',
						'Laura j\'aime gérer <laura@jaime-gerer.com>',
						'Pierre Lebourg <pierre@web4change.com>'
						);
		// boucle sur les listes des contacts
		foreach( $campagne->getListesContact() as $rapport )
		{
			//~ var_dump($rapport);
			$arr_filters = $filterRepo->findByRapport($rapport);
			$arr_obj = $objRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany());

			$i = 0;
			$to = array();
			foreach( $arr_obj as $contact )
			{
				// Résolution des emails
				$to[] = $contact." <".$contact->getEmail().">";
				// Pour ne pas spammer les emails de tests
				break;
				// Envoi par palier de 1000 destinataires
				if( count($to) == 1000 )
				{
					// switch de $to pour les tests
					$to = $TestTo;
					//~ var_dump($to); echo 1; exit;
					$result = $mgClient->sendMessage($domain, array(
								//~ 'from'    => 'mohamed hichem <mohamed.hichem@gmail.com>',
								'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
								'to'      => implode(',', $to),
								'subject' => $campagne->getObjetEmail(),
								'html'    => $campagne->getTemplate(),
								'o:campaign' => $campagne->getId(),
								//~ 'X-Mailgun-Variables' => '{"zzk": "http://www.google.com"}',
							'recipient-variables' => '{"mohamed.hichem@gmail.com": {"first":"hich", "id":1},
						   "hichem_ben_messaoud@yahoo.com": {"first":"ben", "id": 2}}'
							));
					$to = array();
				}
				/*

			$result = $mgClient->sendMessage($domain, array(
						'from'    => 'mohamed hichem <mohamed.hichem@gmail.com>',
						'to'      => 'hich <mohamed.hichem@gmail.com>, ben <fvdfbdbdghrthichem_ben_messaoud@yahoo.com>, hh <sdfsdf@sdfsddgbdtgegs35438641.com>',
						//~ 'to'      => 'hich <mohamed.hichem@gmail.com>, ben <hichem_ben_messaoud@yahoo.com>',
						'subject' => $campagne->getObjetEmail(),
						'html'    => $campagne->getTemplate(),
						'o:campaign' => $campagne->getId(),
						//'If you wish to unsubscribe,
							//				  click http://mailgun/unsubscribe/%recipient.id%',
								'recipient-variables' => '{"mohamed.hichem@gmail.com": {"first":"hich", "id":1},
														   "hichem_ben_messaoud@yahoo.com": {"first":"ben", "id": 2}}'
					));*/
			}

			if( count($to) > 0 )
			{
				$to = $TestTo;
					//~ var_dump($to); echo 2; exit;
				$result = $mgClient->sendMessage($domain, array(
							//~ 'from'    => 'mohamed hichem <mohamed.hichem@gmail.com>',
							'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
							'to'      => implode(',', $to),
							'subject' => $campagne->getObjetEmail(),
							'html'    => $campagne->getTemplate(),
							'o:campaign' => $campagne->getId(),
							//~ 'X-Mailgun-Variables' => '{"zzk": "http://www.google.com"}',
							'recipient-variables' => '{"mohamed.hichem@gmail.com": {"first":"hich", "id":1},
						   "hichem_ben_messaoud@yahoo.com": {"first":"ben", "id": 2}}'
						));
						//~ , "unsubscribe_url": "http://www.google.com"
						//~ echo $to[0]; exit;
			}
			// Pour ne pas spammer les emails de tests
			break;
		}
		$campagne->setDateEnvoi(new \DateTime('now'));
		$campagne->setEnvoyee(true);
		$em = $this->getDoctrine()->getManager();
		$em->persist($campagne);
		$em->flush();
		//~ echo 1;
		//~ exit;
		return $this->redirect($this->generateUrl(
				'emailing_campagne_voir',
				array('id' => $campagne->getId())
		));
	}

	/**
	 * @Route("/emailing/campagne/unsubscribe/{email}", name="emailing_campagne_unsubscribe")
	 */
	public function campagneUnsubscribeAction($email)
	{
		$contactRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$contact = $contactRepo->findOneByEmail($email);
		//~ var_dump($contact);
		$em = $this->getDoctrine()->getManager();
		$contact->setRejetEmail(true);
		$em->persist($contact);
		$em->flush();
		//~ foreach( $contacts as $contact )
		//~ {
			//~ var_dump($contact); exit;
		//~ }
		exit;
	}
	

}
