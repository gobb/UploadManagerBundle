<?php

/*
 * (c) Florian Koerner <f.koerner@checkdomain.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Checkdomain\UploadManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The upload controller manages all file actions except the upload action
 * 
 * @author Florian Koerner <f.koerner@checkdomain.de>
 */
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
