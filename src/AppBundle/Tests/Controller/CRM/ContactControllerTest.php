<?php
namespace AppBundle\Tests\Controller\CRM;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{

	private $client = null;
    private $fixtures = null;

    public function setUp()
    {
    	$this->fixtures = $this->loadFixtures(array(
            'AppBundle\DataFixtures\AppFixtures'
        ))->getReferenceRepository();

        $this->loginAs($this->fixtures->getReference('user-laura'), 'main');
		$this->client = $this->makeClient();
    }

    /**
	 *	Test : affichage fiche contact
	 **/
    public function testContactVoir()
    {
        $crawler = $this->client->request('GET', '/crm/contact/voir/1');
        	
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());	
        $this->assertContains('Geoffroy Murat', $crawler->filter('#compte_details h1')->text());
        $this->assertContains('Directeur', $crawler->filter('#compte_details h1 span.l')->text());
    }

    /**
     *  Test : ajouter un contact par le formulaire
    **/
    public function testContactAjouter(){

        $crawler = $this->client->request('GET', '/crm/contact/ajouter');

        //test : affichage et soumission du formulaire
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Créer un contact', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('btn-submit-form');
        $form = $buttonCrawlerNode->form();
        $form['appbundle_crm_contact[prenom]'] = 'Rabiss';
        $form['appbundle_crm_contact[nom]'] = 'Gilquin';
        $form['appbundle_crm_contact[compte]'] = 2;
        $form['appbundle_crm_contact[titre]'] = 'Assistant';
        $form['appbundle_crm_contact[secteur]'] = $this->fixtures->getReference('secteur-chimie')->getId();
        $form['appbundle_crm_contact[userGestion]'] = $this->fixtures->getReference('user-laura')->getId();
        $form['appbundle_crm_contact[types]'] = $this->fixtures->getReference('type-contact-prospect')->getId();
        $form['appbundle_crm_contact[adresse]'] = '63 rue Yvon Morandat';
        $form['appbundle_crm_contact[codePostal]'] = '73000';
        $form['appbundle_crm_contact[ville]'] = 'Chambéry';
        $form['appbundle_crm_contact[region]'] = 'Auvergne-Rhône-Alpes';
        $form['appbundle_crm_contact[pays]'] = 'France';
        $form['appbundle_crm_contact[telephoneFixe]'] = '0479692947';
        $form['appbundle_crm_contact[email]'] = 'rabiss@democorp.com';
        $form['appbundle_crm_contact[origine]'] = $this->fixtures->getReference('origine-appel-entrant')->getId();
        $form['appbundle_crm_contact[newsletter]']->tick();
        $this->client->submit($form);

        //test : création des valeurs du contact sur la fiche contact
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Rabiss Gilquin', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Assistant', $crawler->filter('h1:not(#titre-outil) span.l')->text());
        $this->assertContains('Chimie, Pharmacie, Cosmétique & Santé', $crawler->filter('#contact-secteur')->text());
        $this->assertContains('63 rue Yvon Morandat', $crawler->filter('#contact-adresse')->text());
        $this->assertContains('Laura Gilquin', $crawler->filter('#contact-user-gestion')->text());
        $this->assertContains('0479692947', $crawler->filter('#contact-tel-fixe')->text());
        $this->assertContains('Appel entrant', $crawler->filter('#contact-origine')->text());
        $this->assertCount(1, $crawler->filter('#contact-newsletter span.glyphicon-ok'));
        $this->assertCount(0, $crawler->filter('#contact-carte-voeux span.glyphicon-ok'));
    }

    /**
     *  Test : modifier un contact par le formulaire
     **/
    public function testContactEditer(){

        $crawler = $this->client->request('GET', '/crm/contact/editer/2');

        //test : affichage et soumission du formulaire
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Modifier un contact', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('btn-submit-form');
        $form = $buttonCrawlerNode->form();
        $form['appbundle_crm_contact[prenom]'] = 'Célina';
        $form['appbundle_crm_contact[nom]'] = 'Gindra';
        $form['appbundle_crm_contact[titre]'] = 'Energy connector';
        $form['appbundle_crm_contact[secteur]'] = $this->fixtures->getReference('secteur-chimie')->getId();
        $form['appbundle_crm_contact[userGestion]'] = $this->fixtures->getReference('user-laura')->getId();
        $form['appbundle_crm_contact[origine]'] = $this->fixtures->getReference('origine-appel-entrant')->getId();
        $form['appbundle_crm_contact[newsletter]']->untick();
        $form['appbundle_crm_contact[carteVoeux]']->tick();
        $this->client->submit($form);

        //test : création des valeurs du contact sur la fiche contact
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Célina Gindra', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Energy connector', $crawler->filter('h1:not(#titre-outil) span.l')->text());
        $this->assertContains('Chimie, Pharmacie, Cosmétique & Santé', $crawler->filter('#contact-secteur')->text());
        $this->assertContains('Laura Gilquin', $crawler->filter('#contact-user-edition')->text());
        $this->assertContains(date('d/m/Y'), $crawler->filter('#contact-date-edition')->text());
        $this->assertContains('Appel entrant', $crawler->filter('#contact-origine')->text());
        $this->assertCount(0, $crawler->filter('#contact-newsletter span.glyphicon-ok'));
        $this->assertCount(1, $crawler->filter('#contact-carte-voeux span.glyphicon-ok'));
    }

     /**
     *  Test : supprimer un contact
     **/
    public function testContactSupprimer(){

        $crawler = $this->client->request('GET', '/crm/contact/supprimer/2');

        //test : affichage et soumission du formulaire
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Supprimer un contact', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Etes-vous sur de vouloir supprimer le contact Céline Gindre ?', $crawler->filter('p#warning-suppression')->text());

        $buttonCrawlerNode = $crawler->selectButton('btn-submit-form');
        $form = $buttonCrawlerNode->form();
        $this->client->submit($form);

        //test : création des valeurs du contact sur la fiche contact
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        $this->assertContains('Contacts', $crawler->filter('h1:not(#titre-outil)')->text());

    }

     /**
	 *	Test : importer un fichier de contacts contenant 1 nouveau compte et 1 nouveau contact
	 **/
    public function testContactImportNouveauCompte()
    {
        $crawler = $this->client->request('GET', '/crm/contact/import/upload');
        
        //test : affichage et soumission du formulaire d'upload du fichier d'import   	
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Importer des contacts', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('form_submit');
        $form = $buttonCrawlerNode->form();
        $form['form[fichier_import]']->upload('web\files\test\crm_import_contact_nouveau.xlsx');
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();

        //test : affichage de l'écran de validation et contrôle des données affichées
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
       
        $this->assertContains('Valider un fichier', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertCount(1, $crawler->filter('#collapse-comptes-non-existant ul li')); 
        $this->assertContains('A la pêche aux moules moules moules', $crawler->filter('#collapse-comptes-non-existant ul li:first-child')->text()); 
        $this->assertCount(1, $crawler->filter('#collapse-contacts-non-existant ul li'));
        $this->assertContains('Madame Colette', $crawler->filter('#collapse-contacts-non-existant ul li:first-child')->text());
        
        //test : soumission du formulaire d'import et contrôle des données affichées
        $form = $crawler->filter('#form_valider')->form();
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertContains('Résultats de l\'import de contacts', $crawler->filter('h1:not(#titre-outil)')->text());
		$this->assertCount(1, $crawler->filter('#collapse-comptes-created ul li'));
		$this->assertContains('A la pêche aux moules moules moules', $crawler->filter('#collapse-comptes-created ul li:first-child')->text());
		$this->assertCount(1, $crawler->filter('#collapse-contacts-created ul li'));
		$this->assertContains('Madame Colette', $crawler->filter('#collapse-contacts-created ul li:first-child')->text());

        //test : création des valeurs du compte sur la fiche organisation
		$link = $crawler
		    ->filter('#collapse-comptes-created ul li:first-child a')
		    ->eq(0)
		    ->link()
		;
		$crawler = $this->client->click($link);
        $this->assertContains('A la pêche aux moules moules moules', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Agriculture & Pêche', $crawler->filter('#compte-secteur')->text());
        $this->assertContains('Saint André', $crawler->filter('#compte-adresse')->text());
		$this->assertContains('Laura Gilquin', $crawler->filter('#compte-user-gestion')->text());

        $this->assertCount(1, $crawler->filter('#table_contacts tbody tr'));

        //test : création des valeurs du contact sur la fiche contact
        $link = $crawler
            ->filter('#table_contacts tbody tr:first-child td.contact-nom a')
            ->eq(0)
            ->link()
        ;
        $crawler = $this->client->click($link);
        $this->assertContains('Madame Colette', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Pêcheuse', $crawler->filter('h1:not(#titre-outil) span.l')->text());
        $this->assertContains('Agriculture & Pêche', $crawler->filter('#contact-secteur')->text());
        $this->assertContains('Saint André', $crawler->filter('#contact-adresse')->text());
        $this->assertContains('Laura Gilquin', $crawler->filter('#contact-user-gestion')->text());
        $this->assertContains('0405060708', $crawler->filter('#contact-tel-fixe')->text());
        $this->assertContains('Web research', $crawler->filter('#contact-origine')->text());
        $this->assertCount(1, $crawler->filter('#contact-newsletter span.glyphicon-ok'));
        $this->assertCount(0, $crawler->filter('#contact-carte-voeux span.glyphicon-ok'));
 
    }

     /**
     *  Test : importer un fichier de contacts contenant des erreurs
     **/
    public function testContactImportErreur()
    {
        $crawler = $this->client->request('GET', '/crm/contact/import/upload');
            
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Importer des contacts', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('form_submit');
        $form = $buttonCrawlerNode->form();
        $form['form[fichier_import]']->upload('web\files\test\crm_import_contact_erreurs.xlsx');
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
       
        $this->assertContains('Valider un fichier', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertCount(5, $crawler->filter('#collapse-contacts-erreurs ul li')); 
        $this->assertContains('Ligne 2 : le réseau "Copines de Thérèse" n\'existe pas', $crawler->filter('#collapse-contacts-erreurs ul li:first-child')->text());
        $this->assertContains('Ligne 2 : l\'origine "Thérèse" n\'existe pas', $crawler->filter('#collapse-contacts-erreurs ul li:nth-child(2)')->text());
        $this->assertContains('Ligne 2 : le service d\'intérêt "Prière" n\'existe pas', $crawler->filter('#collapse-contacts-erreurs ul li:nth-child(3)')->text());
        $this->assertContains('Ligne 2 : le thème d\'intérêt "Dieu" n\'existe pas', $crawler->filter('#collapse-contacts-erreurs ul li:nth-child(4)')->text());
        $this->assertContains('Ligne 2 : le secteur d\'activité "Clergé" n\'existe pas', $crawler->filter('#collapse-contacts-erreurs ul li:nth-child(5)')->text());
        
        $this->assertContains('Votre fichier contient des erreurs.', $crawler->filter('.alert-danger')->text());
        $this->assertCount(0, $crawler->filter('#form_valider'));

    }

     /**
     *  Test : importer un fichier de contacts contenant la mise à jour d'un compte et d'un contact, et un doublon
     **/
    public function testContactImportUpdateCompte()
    {
        $crawler = $this->client->request('GET', '/crm/contact/import/upload');
            
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('Importer des contacts', $crawler->filter('h1:not(#titre-outil)')->text());

        $buttonCrawlerNode = $crawler->selectButton('form_submit');
        $form = $buttonCrawlerNode->form();
        $form['form[fichier_import]']->upload('web\files\test\crm_import_contact_update.xlsx');
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
       
        $this->assertContains('Valider un fichier', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertCount(1, $crawler->filter('#collapse-comptes-existant ul li')); 
        $this->assertContains('DemoCorp', $crawler->filter('#collapse-comptes-existant ul li:first-child')->text()); 
        $this->assertCount(1, $crawler->filter('#collapse-contacts-existant ul li'));
        $this->assertContains('Potiron Groschat', $crawler->filter('#collapse-contacts-existant ul li:first-child')->text());
        $this->assertCount(1, $crawler->filter('#collapse-contacts-doublons ul li'));
        $this->assertContains('Potiron Groschat', $crawler->filter('#collapse-contacts-doublons ul li:first-child')->text());
        
        $form = $crawler->filter('#form_valider')->form();
        $form['form[update]']->tick();
        $crawler = $this->client->submit($form);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->assertContains('Résultats de l\'import de contacts', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertCount(1, $crawler->filter('#collapse-comptes-updated ul li'));
        $this->assertContains('DemoCorp', $crawler->filter('#collapse-comptes-updated ul li:first-child')->text());
        $this->assertCount(1, $crawler->filter('#collapse-contacts-updated ul li'));
        $this->assertContains('Potiron Groschat', $crawler->filter('#collapse-contacts-updated ul li:first-child')->text());

        $link = $crawler
            ->filter('#collapse-comptes-updated ul li:first-child a')
            ->eq(0)
            ->link()
        ;
        $crawler = $this->client->click($link);
        $this->assertContains('DemoCorp', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Agriculture & Pêche', $crawler->filter('#compte-secteur')->text());
        $this->assertContains('385 avenue de la Motte Servolex', $crawler->filter('#compte-adresse')->text());

        $this->assertCount(1, $crawler->filter('#table_contacts tbody tr'));

        //test : création des valeurs du contact sur la fiche contact
        $link = $crawler
            ->filter('#table_contacts tbody tr:first-child td.contact-nom a')
            ->eq(0)
            ->link()
        ;
        $crawler = $this->client->click($link);
        $this->assertContains('Potiron Groschat', $crawler->filter('h1:not(#titre-outil)')->text());
        $this->assertContains('Big boss', $crawler->filter('h1:not(#titre-outil) span.l')->text());
        $this->assertContains('Agriculture & Pêche', $crawler->filter('#contact-secteur')->text());
        $this->assertContains('385 avenue de la Motte Servolex', $crawler->filter('#contact-adresse')->text());
        $this->assertContains('Web research', $crawler->filter('#contact-origine')->text());
        $this->assertContains('Ruche qui dit oui!', $crawler->filter('#contact-reseau')->text());
        $this->assertCount(0, $crawler->filter('#contact-newsletter span.glyphicon-ok'));
        $this->assertCount(1, $crawler->filter('#contact-carte-voeux span.glyphicon-ok'));   
    }


}
