<?php

namespace Checkdomain\UploadManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $form = $this->createForm(new \Checkdomain\UploadManagerBundle\Form\Type\TestType());
        
        return $this->render('CheckdomainUploadManagerBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
