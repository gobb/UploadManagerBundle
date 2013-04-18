<?php

namespace Checkdomain\UploadManagerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('test', 'upload', array());
    }
    
    public function getName()
    {
        return 'test';
    }
}