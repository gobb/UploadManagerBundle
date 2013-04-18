<?php

namespace Checkdomain\UploadManagerBundle\Exception;

class InstanceNotFoundException extends \Exception
{
    protected $message = 'Invalid unique upload id. The instance could not be found.';
}