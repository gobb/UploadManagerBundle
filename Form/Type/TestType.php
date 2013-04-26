<?php

/*
 * (c) Florian Koerner <f.koerner@checkdomain.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Checkdomain\UploadManagerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Test formular
 * 
 * @author Florian Koerner <f.koerner@checkdomain.de>
 */
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
            'upload_dir' => NULL,
            'label'  => 'Your documents',
        ));
    }
    
    public function getName()
    {
        return 'test';
    }
}