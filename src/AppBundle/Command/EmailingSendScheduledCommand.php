<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Service\Emailing\MailgunService;

class EmailingSendScheduledCommand extends ContainerAwareCommand
{

    private $em;
    private $mailgunService;

    public function __construct(EntityManagerInterface $em, MailgunService $mailgunService)
    {
        parent::__construct();
        $this->em = $em;
        $this->mailgunService = $mailgunService;
    }


    protected function configure()
    {
        $this
            ->setName('jg:emailing-send-scheduled')
            ->setDescription('Send scheduled campaigns to MailGun');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getContainer()->get('monolog.logger.cron_logger');

        $campagneRepo = $this->em->getRepository('AppBundle:Emailing\Campagne');
        $today = date('Y-m-d');

        $arr_campagnes = $campagneRepo->findBy(array(
            'etat' => 'SCHEDULED',
            'dateEnvoi' => \DateTime::createFromFormat('Y-m-d', $today)
        ));

        $logger->info(count($arr_campagnes).' campagnes à envoyer à MailGun.');

        foreach($arr_campagnes as $campagne){

            try{
                $this->mailgunService->sendCampagneViaAPI($campagne);

                $campagne->setEtat('DELIVERING');
         
                $this->em->persist($campagne);
                $this->em->flush();

                $logger->info('--- '.$campagne->getId().' envoyée à MailGun.');
            } catch(\Exception $e){
                $logger->error('--- Erreur lors de l\'envoi de la campagne '.$campagne->getId().'  à MailGun : '.$e->getMessage());
                
                $campagne->setEtat('ERROR');

                $this->em->persist($campagne);
                $this->em->flush();
            }
        }
      
    }

}
