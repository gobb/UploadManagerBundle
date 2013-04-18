<?php

namespace Checkdomain\UploadManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $service = $this->get('upload_manager');
        
        $service->newInstance('todo/message/1');
        $service->removeFile('haha.php')
                ->removeFile('haha2.php');
        $service->addFile('/Users/Florian/GitHub/KoernerWS/UploadManagerBundle/web/app.php');
        $service->synchronise();
        
        print_r($service->getFilesByStatus());
        
        
        $form = $this->createForm(new \Checkdomain\UploadManagerBundle\Form\Type\TestType());
        
        return $this->render('CheckdomainUploadManagerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
