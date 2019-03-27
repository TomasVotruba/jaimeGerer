<?php

namespace AppBundle\Service\CRM;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class TodoListService extends ContainerAware {

    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function createTodoList($user){
    	$todoList = array();

		$arr_timespans = array('today', 'late', 'week');
		$arr_types = array('FACTURER', 'CONTACTER');

		foreach($arr_timespans as $timespan){
			$todoList[$timespan] = array();

			foreach($arr_types as $type){
				$tasks = $this->findTasks($timespan, $type, $user);
				foreach($tasks as $tasks){
					$todoList[$timespan][] = array(
						'type' => $type,
						'task' => $tasks
					);
				}
			}

		}

        usort($todoList['week'],  array($this, "sortByDate"));

		return $todoList;
    }

    private function findTasks($timespan, $type, $user){

    	$repo = $this->em->getRepository('AppBundle:CRM\Impulsion');
    	if('FACTURER' == $type){
    		$repo = $this->em->getRepository('AppBundle:CRM\PlanPaiement');
    	}
    	
    	return $repo->findTimespan($timespan, $user);
    }

    private function sortByDate($a, $b){
        if ($a['task']->getDate() == $b['task']->getDate()) {
                return 0;
            }
            return ($a['task']->getDate() < $b['task']->getDate()) ? -1 : 1;
    }

    


}