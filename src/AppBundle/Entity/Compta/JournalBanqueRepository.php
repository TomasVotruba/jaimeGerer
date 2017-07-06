<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\EntityRepository;

/**
 * JournalBanqueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class JournalBanqueRepository extends EntityRepository
{
	public function findJournalEntier($company, $compteBancaire, $year){

		$result = $this->createQueryBuilder('j')
		->leftJoin('AppBundle\Entity\Compta\CompteComptable', 'c', 'WITH', 'j.compteComptable = c.id')
		->leftJoin('AppBundle\Entity\Compta\MouvementBancaire', 'm', 'WITH', 'j.mouvementBancaire = m.id')
		->where('c.company = :company')
		->andWhere('m.compteBancaire = :compteBancaire')
		->andWhere('(j.date >= :startDate and j.date <= :endDate)')
		->setParameter('startDate', $year.'-01-01')
		->setParameter('endDate', $year.'-12-31')
		->setParameter('company', $company)
		->setParameter('compteBancaire', $compteBancaire)
		->orderBy('j.id', 'DESC')
		->addOrderBy('j.debit', 'DESC')
		->getQuery()
		->getResult();

		return $result;
	}

	public function findByCompteForCompany($compteComptable, $company, $startDate = null, $endDate = null){

		$qb = $this->createQueryBuilder('j')
		->leftJoin('AppBundle\Entity\Compta\CompteComptable', 'c', 'WITH', 'j.compteComptable = c.id')
		->where('c.company = :company')
		->andWhere('j.compteComptable = :compteComptable')
		->setParameter('company', $company)
		->setParameter('compteComptable', $compteComptable);

		if($startDate && $endDate){
			$qb->andWhere('j.date >= :startDate and j.date <= :endDate')
					->setParameter('startDate', $startDate)
					->setParameter('endDate', $endDate);
		}

		$qb->orderBy('j.date', 'ASC')
		->addOrderBy('j.debit', 'DESC');

		$result = $qb->getQuery()
		->getResult();

		return $result;
	}

	public function findForCompany($company){

		$qb = $this->createQueryBuilder('j')
		->leftJoin('AppBundle\Entity\Compta\CompteComptable', 'c', 'WITH', 'j.compteComptable = c.id')
		->where('c.company = :company')
		->setParameter('company', $company)
		->orderBy('j.date', 'ASC')
		->addOrderBy('j.debit', 'DESC');

		$result = $qb->getQuery()
		->getResult();

		return $result;
	}
}
