<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
          new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
          new Symfony\Bundle\SecurityBundle\SecurityBundle(),
          new Symfony\Bundle\TwigBundle\TwigBundle(),
          new Symfony\Bundle\MonologBundle\MonologBundle(),
          new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
          new Symfony\Bundle\AsseticBundle\AsseticBundle(),
          new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
          new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
          new AppBundle\AppBundle(),
        	new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),
        	new Shtumi\UsefulBundle\ShtumiUsefulBundle(),
        	new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
        	new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
        	new Liuggio\ExcelBundle\LiuggioExcelBundle(),
        	new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
        	new cspoo\Swiftmailer\MailgunBundle\cspooSwiftmailerMailgunBundle(),
        	new Fenrizbes\ColorPickerTypeBundle\FenrizbesColorPickerTypeBundle(),
        	new RC\AmChartsBundle\RCAmChartsBundle(),
        	new FOS\UserBundle\FOSUserBundle(),
        	new Nicomak\UserBundle\NicomakUserBundle(),
        	new Sonata\UserBundle\SonataUserBundle('NicomakUserBundle'),
        	new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        	new Knp\Bundle\MenuBundle\KnpMenuBundle(),
        	new Sonata\CoreBundle\SonataCoreBundle(),
        	new Sonata\BlockBundle\SonataBlockBundle(),
        	new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
        	new Sonata\AdminBundle\SonataAdminBundle(),
        	new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
        	new Sonata\IntlBundle\SonataIntlBundle(),
          new Nicomak\PaypalBundle\NicomakPaypalBundle(),
          new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
          new Snowcap\ImBundle\SnowcapImBundle(),
          new CMEN\GoogleChartsBundle\CMENGoogleChartsBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
