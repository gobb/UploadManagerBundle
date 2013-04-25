<?php

/*
 * (c) Florian Koerner <f.koerner@checkdomain.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Checkdomain\UploadManagerBundle\Exception;

/**
 * @author Florian Koerner <f.koerner@checkdomain.de>
 */
class ValidatorException extends \Exception
{
    protected $message = 'Validation failed.';
    protected $errors = array();
    
    public function __construct($errors)
    {
        $this->setErrors($errors);
        parent::__construct();
    }
    
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getErrorMessages()
    {
        $errors = array();
        foreach ($this->getErrors() AS $error)
        {
            if (is_string($error))
            {
                $errors[] = $error;
            }
            else
            {
                $errors[] = $error->getMessage();
            }
        }
        return $errors;
    }
}