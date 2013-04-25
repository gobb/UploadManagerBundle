<?php

namespace Checkdomain\UploadManagerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TestType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'upload_url'
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('files_unique_id', 'upload', array(
            'upload_url' => $options['upload_url'],
            'upload_dir' => '/user/documents/',
            'label'  => 'Your documents',
        ));
    }
    
    public function getName()
    {
        return 'test';
    }
}