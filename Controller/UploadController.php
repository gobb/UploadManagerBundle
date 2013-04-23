<?php

namespace Checkdomain\UploadManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class UploadController extends Controller
{
    /**
     * @Route("/_upload/")
     * @Method("POST")
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $upload_manager = $this->get('upload_manager');
        $upload_manager->getInstance($request->get('unique_id'));
        
        switch ($request->get('action'))
        {
            case 'delete_existing':
                $upload_manager->removeFile($request->get('file'));
                break;
            
            case 'delete_added':
                $upload_manager->removeTempFile($request->get('file'));
                break;
            
            case 'restore_deleted':
                $upload_manager->restoreFile($request->get('file'));
                break;
            default:
                throw new NotFoundHttpException();
        }
        
        return new Response('success');
    }
}
