<?php

namespace Checkdomain\UploadManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $form = $this->createForm(new \Checkdomain\UploadManagerBundle\Form\Type\TestType(), NULL, array(
            'upload_url' => $this->get('router')->generate('checkdomain_uploadmanager_homepage_upload')
        ));
        $message = NULL;
        
        if ($this->getRequest()->isMethod('POST'))
        {
            $form->bind($this->getRequest());
            
            if ($form->isValid())
            {
                $uploadmanager = $this->get('upload_manager');
                $uploadmanager->getInstance($form->get('files_unique_id')->getData());
                $uploadmanager->synchronise();
                
                $message = 'Die Dateien wurden erfolgreich gespeichert!';
            }
        }
        
        return $this->render('CheckdomainUploadManagerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
            'message' => $message
        ));
    }
    
    public function uploadAction()
    {
        $upload_manager = $this->get('upload_manager');
        
        $upload_manager->getInstance($this->getRequest()->get('unique_id'));
        
        $upload_manager->setConstraints(array(
            new \Symfony\Component\Validator\Constraints\NotNull(),
            new \Symfony\Component\Validator\Constraints\File(),
            new \Symfony\Component\Validator\Constraints\Image()
        ));
        
        $file = $upload_manager->addFile($this->getRequest()->files->get('file'));
        
        return new Response(json_encode(array(
            'filename' => $file
        )));
    }
}