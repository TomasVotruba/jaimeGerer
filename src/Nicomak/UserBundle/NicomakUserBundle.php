<?php

namespace Nicomak\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NicomakUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
