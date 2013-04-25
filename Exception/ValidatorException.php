<?php

namespace Checkdomain\UploadManagerBundle\Exception;

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