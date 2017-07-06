<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\EntityRepository;

/**
 * AffectationDiverseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AffectationDiverseRepository extends EntityRepository
{
	public function findForCompany($type, $company, $recurrent = null){
		$qb = $this->createQueryBuilder('a')
		->leftJoin('AppBundle\Entity\Compta\CompteComptable', 'c', 'WITH', 'c.id = a.compteComptable')
		->where('c.company = :company')
		->andWhere('a.type = :type')
		->setParameter('company', $company)
		->setParameter('type', $type);
		
		if($recurrent != null){
			$qb->andWhere('a.recurrent = :recurrent')
			->setParameter('recurrent', $recurrent);
		}	
		return $qb->getQuery()->getResult();
	}
}