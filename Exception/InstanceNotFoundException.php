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
class InstanceNotFoundException extends \Exception
{
    protected $message = 'Invalid unique upload id. The instance could not be found.';
}