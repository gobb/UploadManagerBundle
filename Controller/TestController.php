<?php

namespace Checkdomain\UploadManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/uploadmanager")
 */
class TestController extends Controller
{
    /**
     * Shows, validates and saves the form data
     * 
     * @Route("/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // Create test form
        $form = $this->createForm(new \Checkdomain\UploadManagerBundle\Form\Type\TestType(), NULL, array(
            // controller upload action
            'upload_url' => $this->generateUrl('checkdomain_uploadmanager_test_upload')
        ));
        
        // We got some POST data
        if ($this->getRequest()->isMethod('POST'))
        {
            // Bind request to the form
            $form->bind($this->getRequest());
            
            // Validate form
            if ($form->isValid())
            {
                // Get upload_manager
                $uploadmanager = $this->get('upload_manager');
                
                // Get instance by post data (field name from "test type")
                $uploadmanager->getInstance($form->get('files_unique_id')->getData());
                
                // Synchronise uploaded and deleted with existing files
                $uploadmanager->synchronise();
            }
        }
        
        // Render the view
        return $this->render('CheckdomainUploadManagerBundle:Test:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * Validates a file and adds it to the "upload_manager" instance.
     * 
     * @Route("/upload/")
     * @Method("POST")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction()
    {
        // Get upload_manager service
        $upload_manager = $this->get('upload_manager');
        
        // Get instance with request post data (always "unqiue_id")
        $upload_manager->getInstance($this->getRequest()->get('unique_id'));
        
        // Set constraints to validate
        $upload_manager->setConstraints(array(
            new \Symfony\Component\Validator\Constraints\NotNull(),
            new \Symfony\Component\Validator\Constraints\File(array(
                'maxSize' => '200k'
            )),
            new \Symfony\Component\Validator\Constraints\Image()
        ));
        
        // Try to add a file and build response array
        try {
            $response = array(
                'data' => $upload_manager->addFile($this->getRequest()->files->get('file'))
            );
        } catch (\Checkdomain\UploadManagerBundle\Exception\ValidatorException $e) {
            $response = array(
                'errors' => $e->getErrorMessages()
            );
        }
        
        // Create a json response
        return new Response(json_encode($response));
    }
}