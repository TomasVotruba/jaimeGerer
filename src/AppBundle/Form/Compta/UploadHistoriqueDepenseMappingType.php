<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

class UploadHistoriqueDepenseMappingType extends AbstractType
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
    	$compte = "";
    	$num = "";
    	$date = "";
    	$dateCreation = "";
    	$etat = "";
    	$user = "";
    	$analytique = "";
    	$modePaiement = "";
    	$taxe = "";
    	
    	$produitNom = "";
    	$produitTarif = "";
    	$produitTaxe = "";
    	$produitCompteComptable = "";
    	
    	foreach($this->arr_headers as $header){
    		
    		
    		if(stristr($header, 'fournisseur')){
    			$compte = $header;
    		}
    		if(stristr($header, 'date')){
    			$date = $header;
    		}
    		if(stristr($header, 'création')){
    			$dateCreation = $header;
    		}
    		
    		if(stristr($header, 'tva')){
    			if(stristr($header, 'total')){
    				$tva = $header;
    			} else{
    				$tauxTVA = $header;
    			}
    		}
    		if(stristr($header, 'remise')){
    			$remise = $header;
    		}
    		
    		if(stristr($header, 'état') || stristr($header, 'etat')){
    			$etat = $header;
    		}
    		
    		if(stristr($header, 'métier') || stristr($header, 'metier')){
    			$analytique = $header;
    		}
    		
    		if(stristr($header, 'ht')){
    			$produitTarif = $header;
    		}
    		
    		if(stristr($header, 'créateur')){
    			$user = $header;
    		}
    		
    		if(stristr($header, 'paiement')){
    			$modePaiement = $header;
    		}
    		
    		if(stristr($header, 'numéro') || stristr($header, 'n°') || stristr($header, 'référence') || stristr($header, 'reference')){
    			$num = $header;
    		}
    		
    		if(stristr($header, 'classification') ){
    			$produitNom = $header;
    			$produitCompteComptable = $header;
    		}
    		if(stristr($header, 'HT') || stristr($header, 'montant') ){
    			$produitTarif = $header;
    		}
    		if(stristr($header, 'taxe') || stristr($header, 'TVA') ){
    			$produitTaxe = $header;
    			$taxe = $header;
    		}
    		if(stristr($header, 'HT') || stristr($header, 'montant') ){
    			$produitTarif = $header;
    		}
    	}

    	$arr_formats = array(
    			'd/m/Y' => 'jour/mois/année (4 chiffres)',
    			'd/m/y'=> 'jour/mois/année (2 chiffres)',
    			'd-m-Y' => 'jour-mois-année (4 chiffres)',
    			'd-m-y'=> 'jour-mois-année (2 chiffres)',
    	);
    	
       	$builder
	       	->add('dateFormat', 'choice', array(
	       			'label'	=> 'Format des dates du fichier importé',
	       			'required' => true,
	       			'choices' => $arr_formats
	       	))
			->add('filepath', 'hidden', array(
					'data' => $this->filename,
			
			))
			->add('compte', 'choice', array(
					'required' => true,
					'label' => 'Fournisseur',
					'choices' => $this->arr_headers,
					'data' => $compte
			))
			->add('num', 'choice', array(
					'required' => true,
					'label' => 'Numéro de dépense',
					'choices' => $this->arr_headers,
					'data' => $num
			))
			->add('date', 'choice', array(
					'required' => true,
					'label' => 'Date de la dépense',
					'choices' => $this->arr_headers,
					'data' => $date
			))
			->add('dateCreation', 'choice', array(
					'required' => true,
					'label' => 'Date de création',
					'choices' => $this->arr_headers,
					'data' => $dateCreation
			))
			->add('etat', 'choice', array(
					'required' => true,
					'label' => 'Etat',
					'choices' => $this->arr_headers,
					'data' => $etat
			))
			->add('modePaiement', 'choice', array(
					'required' => true,
					'label' => 'Mode de paiement',
					'choices' => $this->arr_headers,
					'data' => $modePaiement
			))
			->add('user', 'choice', array(
					'required' => false,
					'label' => 'Créateur',
					'choices' => $this->arr_headers,
					'data' => $user
			))
			->add('taxe', 'choice', array(
					'required' => true,
					'label' => 'Taxe',
					'choices' => $this->arr_headers,
					'data' => $taxe
			))
			->add('analytique', 'choice', array(
					'required' => true,
					'label' => 'Analytique',
					'choices' => $this->arr_headers,
					'data' => $analytique
			))
			->add('produitNom', 'choice', array(
					'required' => true,
					'label' => 'Nom',
					'choices' => $this->arr_headers,
					'data' => $produitNom
			))
			->add('produitTarif', 'choice', array(
					'required' => true,
					'label' => 'Tarif',
					'choices' => $this->arr_headers,
					'data' => $produitTarif
			))
			->add('produitTaxe', 'choice', array(
					'required' => true,
					'label' => 'Taxe',
					'choices' => $this->arr_headers,
					'data' => $produitTaxe
			))
			->add('produitCompteComptable', 'choice', array(
					'required' => true,
					'label' => 'Compte comptable',
					'choices' => $this->arr_headers,
					'data' => $produitCompteComptable
			))
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
        return 'appbundle_compta_uploadhistoriquedepensemapping';
    }
}
