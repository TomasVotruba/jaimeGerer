<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

class CompteImportMappingType extends AbstractType
{
	
	protected $arr_headers;
	protected $filename;
	
	public function __construct ($arr_headers = null, $filename = null)
	{
		$this->arr_headers = $arr_headers;
		$this->filename = $filename;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$nom = "";
    	$adresse = "";
    	$codePostal = "";
    	$ville = "";
    	$region = "";
    	$pays = "";
    	$telephone = "";
    	$fax = "";
    	$url = "";
    	$description = "";
    	
//     	foreach($this->arr_headers as $header){
    		
//     		if(stristr($header, 'objet')){
//     			$objet = $header;
//     		} 
//     		if(stristr($header, 'numéro') || stristr($header, 'n°')){
//     			$num = $header;
//     		} 
//     		if(stristr($header, 'client')){
//     			$compte = $header;
//     		}
//     		if(stristr($header, 'date')){
//     			$date = $header;
//     		}
//     		if(stristr($header, 'échéance') || stristr($header, 'echeance') || stristr($header, 'validité') || stristr($header, 'validite')){
//     			$echeance = $header;
//     		}
//     		if(stristr($header, 'tva')){
//     			if(stristr($header, 'total')){
//     				$tva = $header;
//     			} else{
//     				$tauxTVA = $header;
//     			}
//     		}
//     		if(stristr($header, 'remise')){
//     			$remise = $header;
//     		}
//     		if(stristr($header, 'description')){
//     			$description = $header;
//     		}
//     		if(stristr($header, 'état') || stristr($header, 'etat')){
//     			$etat = $header;
//     		}
    		
//     		if(stristr($header, 'métier') || stristr($header, 'metier')){
//     			$produitType = $header;
//     		}
//     		if(stristr($header, 'désignation') || stristr($header, 'designation')){
//     			$produitDescription = $header;
//     		}
//     		if(stristr($header, 'ht')){
//     			$produitTarif = $header;
//     		}
//     		if(stristr($header, 'quantité') || stristr($header, 'qté')){
//     			$produitQuantite = $header;
//     		}
//     		if(stristr($header, 'créateur')){
//     			$user = $header;
//     		}
//     	}

       	$builder
			->add('filepath', 'hidden', array(
					'data' => $this->filename,
			
			))
// 			->add('objet', 'choice', array(
// 					'required' => true,
// 					'label' => 'Objet',
// 					'choices' => $this->arr_headers,
// 					'data' => $objet
// 			))
// 			->add('num', 'choice', array(
// 					'required' => true,
// 					'label' => 'Numéro de facture',
// 					'choices' => $this->arr_headers,
// 					'data' => $num
// 			))
// 			->add('compte', 'choice', array(
// 					'required' => true,
// 					'label' => 'Compte',
// 					'choices' => $this->arr_headers,
// 					'data' => $compte
// 			))
// 			->add('date', 'choice', array(
// 					'required' => true,
// 					'label' => 'Date de la facture',
// 					'choices' => $this->arr_headers,
// 					'data' => $date
// 			))
// 			->add('echeance', 'choice', array(
// 					'required' => true,
// 					'label' => 'Echeance',
// 					'choices' => $this->arr_headers,
// 					'data' => $echeance
// 			))
// 			->add('tva', 'choice', array(
// 					'required' => true,
// 					'label' => 'Montant TVA',
// 					'choices' => $this->arr_headers,
// 					'data' => $tva
// 			))
// 			->add('tauxTVA', 'choice', array(
// 					'required' => true,
// 					'label' => 'Taux TVA',
// 					'choices' => $this->arr_headers,
// 					'data' => $tauxTVA
// 			))
// 			->add('remise', 'choice', array(
// 					'required' => true,
// 					'label' => 'Remise',
// 					'choices' => $this->arr_headers,
// 					'data' => $remise
// 			))
// 			->add('description', 'choice', array(
// 					'required' => false,
// 					'label' => 'Description',
// 					'choices' => $this->arr_headers,
// 					'data' => $description
// 			))
// 			->add('etat', 'choice', array(
// 					'required' => true,
// 					'label' => 'Etat',
// 					'choices' => $this->arr_headers,
// 					'data' => $etat
// 			))
// 			->add('user', 'choice', array(
// 					'required' => false,
// 					'label' => 'Créateur',
// 					'choices' => $this->arr_headers,
// 					'data' => $user
// 			))
// 			->add('produitType', 'choice', array(
// 					'required' => true,
// 					'label' => 'Type',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitType
// 			))
// 			->add('produitNom', 'choice', array(
// 					'required' => true,
// 					'label' => 'Nom',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitNom
// 			))
// 			->add('produitDescription', 'choice', array(
// 					'required' => true,
// 					'label' => 'Description',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitDescription
// 			))
// 			->add('produitTarif', 'choice', array(
// 					'required' => true,
// 					'label' => 'Tarif',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitTarif
// 			))
// 			->add('produitQuantite', 'choice', array(
// 					'required' => true,
// 					'label' => 'Quantité',
// 					'choices' => $this->arr_headers,
// 					'data' => $produitQuantite
// 			))
			->add('submit','submit', array(
					'label' => 'Suite',
					'attr' => array('class' => 'btn btn-success')
			))
			;

    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_importcomptemapping';
    }
}
