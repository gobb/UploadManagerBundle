<?php

namespace Checkdomain\UploadManagerBundle\Exception;

class InstanceAlreadyExistsException extends \Exception
{
    protected $message = 'There already exists an instance in this object.';
}