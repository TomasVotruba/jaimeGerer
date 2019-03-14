<?php
namespace AppBundle\Service\CRM;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ActionCommercialeFileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, $user)
    {
        $date = new \DateTime();
        try{
            $fileName = $user->getId().$date->getTimestamp().'-'.$file->getClientOriginalName();
            $file->move($this->targetDirectory, $fileName);
        } catch(\Exception $e){
            throw $e;
        }
        return $fileName;
    }

}