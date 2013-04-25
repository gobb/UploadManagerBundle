<?php

/*
 * (c) Florian Koerner <f.koerner@checkdomain.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Checkdomain\UploadManagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Checkdomain\UploadManagerBundle\Exception\InstanceAlreadyExistsException;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Upload form field type
 * 
 * @author Florian Koerner <f.koerner@checkdomain.de>
 */
class UploadType extends AbstractType
{
    protected $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'upload_url',
            'upload_dir'
        ));
    }
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $upload_manager = $this->container->get('upload_manager');
        
        // Try to create an old instance
        if ($view->get('value'))
        {
            try {
                $upload_manager->getInstance($view->get('value'));
            } catch (InstanceAlreadyExistsException $e) {
                // who cares?
            }
        }
        
        // We need a new instance
        if (!$upload_manager->getUniqueID())
        {
            $upload_manager->newInstance($options['upload_dir']);
        }
        
        $view->set('upload_url', $options['upload_url'])
             ->set('upload_actions', $this->container->get('router')->generate('checkdomain_uploadmanager_upload_index'))
             ->set('value', $upload_manager->getUniqueID())
             ->set('files', $upload_manager->getFilesByStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }
    
    public function getName()
    {
        return 'upload';
    }
}