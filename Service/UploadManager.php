<?php

namespace Checkdomain\UploadManagerBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Validator\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Validator\Constraints\NotNull as NotNullConstraint;
use Symfony\Component\Validator\Constraints\Collection as CollectionConstraint;

use Checkdomain\UploadManagerBundle\Exception\InstanceAlreadyExistsException;
use Checkdomain\UploadManagerBundle\Exception\InstanceNotFoundException;

class UploadManager
{
    const DATA_FILE = '.information.json';
    
    const FILE_STATUS_EXISTING = 'existing';
    const FILE_STATUS_REMOVED = 'deleted';
    const FILE_STATUS_ADDED = 'added';
    
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem = NULL;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder = NULL;
    
    /**
     * @var \Symfony\COmponent\Validator\Validator
     */
    protected $validator = NULL;

    // Configuration
    protected $write_to = NULL;
    protected $upload_path = NULL;
    protected $temp_upload_path = NULL;
    
    // Unique upload id
    protected $unique_id = NULL;
    
    // JSON data
    protected $data = NULL;
    
    // Validators
    protected $constraints = array();

    /**
     * Create an new instance
     * 
     * @param string $dest_directory destination directory
     */
    public function newInstance($dest_directory)
    {
        if ($this->getUniqueID())
        {
            throw new InstanceAlreadyExistsException();
        }
        
        // Set unique id
        $this->setUniqueID(mt_rand(100, 999) . time());
        
        // Set and save data
        $this->setData(array(
            'dest_directory' => $dest_directory,
            'removed_files' => array()
        ));
    }
    
    /**
     * Get an existing instance
     * 
     * @param int $unique_id
     */
    public function getInstance($unique_id)
    {
        // Load instance data
        $data = $this->loadData($unique_id);
        
        // Set data
        $this->setData($data);
        
        // Set unique id
        $this->setUniqueID($unique_id);
    }
    
    /**
     * Set the unique upload id
     * 
     * @param int $id
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    protected function setUniqueID($id)
    {
        $this->unique_id = $id;
        return $this;
    }
    
    /**
     * Get the unique upload id
     * 
     * @return int
     */
    public function getUniqueID()
    {
        return $this->unique_id;
    }
    
    /**
     * Set filesystem service
     * 
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }
    
    /**
     * Get filesystem service
     * 
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
    
    /**
     * Set finder service
     * 
     * @param \Symfony\Component\Finder\Finder $finder
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setFinder(Finder $finder)
    {
        $this->finder = $finder;
        return $this;
    }
    
    /**
     * Get finder service
     * 
     * @return \Symfony\Component\Finder\Finder
     */
    public function getFinder()
    {
        return clone $this->finder;
    }
    
    /**
     * Set validator service
     * 
     * @param \Symfony\Component\Validator\Validator $validator
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
        return $this;
    }
    
    /**
     * Get validator service
     * 
     * @return type
     */
    public function getValidator()
    {
        return $this->validator;
    }
    
    /**
     * Set "write_to" option
     * 
     * @param string $write_to
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setWriteTo($write_to)
    {
        $this->write_to = $write_to;
        return $this;
    }
    
    /**
     * Get "write_to" option
     * 
     * @return string
     */
    public function getWriteTo()
    {
        return $this->write_to;
    }
    
    /**
     * Set file constraints
     */
    public function setConstraints(array $constraints)
    {
        // Add NOT_NULL constraint
        $constraints[] = new NotNullConstraint();
        
        $this->constraints = $constraints;
        return $this;
    }
    
    /**
     * Get file validators
     */
    public function getConstraints()
    {
        return $this->constraints;
    }
    
    /**
     * Set "upload_path" option
     * 
     * @param string $upload_path
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setUploadPath($upload_path)
    {
        $this->upload_path = $upload_path;
        return $this;
    }
    
    /**
     * Get "upload_path" option
     * 
     * @return string
     */
    public function getUploadPath()
    {
        return $this->upload_path;
    }
    
    /**
     * Set "temp_upload_path" option
     * 
     * @param string $temp_upload_path
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setTempUploadPath($temp_upload_path)
    {
        $this->temp_upload_path = $temp_upload_path;
        return $this;
    }
    
    /**
     * Get "temp_upload_path" option
     * 
     * @return string
     */
    public function getTempUploadPath()
    {
        return $this->temp_upload_path;
    }
    
    /**
     * Get absolute path to the upload directory
     */
    public function getAbsoluteUploadPath()
    {
        $data = $this->getData();
        
        $dir = implode('/', array(
            $this->getWriteTo(),
            $this->getUploadPath(),
            $data['dest_directory']
        ));
        
        if (!is_file($dir))
        {
            $this->filesystem->mkdir($dir);
        }
        
        return realpath($dir);
    }
    
    /**
     * Get absolute path to the temporary upload directory
     */
    public function getAbsoluteTempUploadPath($unique_id = NULL)
    {
        $dir = implode('/', array(
            $this->getWriteTo(),
            $this->getTempUploadPath(),
            ($unique_id) ?: $this->getUniqueID()
        ));
        
        if (!is_file($dir))
        {
            $this->filesystem->mkdir($dir);
        }
        
        return realpath($dir);
    }
    
    /**
     * Load the temporary data
     * 
     * @return array
     */
    public function loadData($unique_id = NULL)
    {
        $file = $this->getAbsoluteTempUploadPath($unique_id) . '/' . self::DATA_FILE;
        
        if (!file_exists($file))
        {
            throw new InstanceNotFoundException();
        }
        
        return json_decode(file_get_contents($file), TRUE);
    }
    
    /**
     * Save the temporary data
     * 
     * @return bool
     */
    public function saveData()
    {
        $data = $this->getData();
        $data['time'] = time();
        
        $file = $this->getAbsoluteTempUploadPath() . '/' . self::DATA_FILE;
        return (file_put_contents($file, json_encode($data)) !== FALSE);
    }
    
    /**
     * Set the temporary data
     * 
     * @param string $directory
     * @param bool $save
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function setData($data, $save = TRUE)
    {
        $this->data = $data;
        
        if ($save)
        {
            $this->saveData();
        }
        
        return $this;
    }
    
    /**
     * Get the temporary data
     * 
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Adds a file to the temporary directory
     * 
     * @param string $file
     * @return string
     */
    public function addFile($file)
    {
        // Validate file
        $collection = new CollectionConstraint(array(
            'fields' => array(
                'file' => $this->getConstraints()
            )
        ));
        
        $errors = $this->getValidator()->validateValue(array(
            'file' => $file
        ), $collection);
        
        if (count($errors))
        {
            // There are some errors
        }
        
        // Add file
        $upload_path = $this->getAbsoluteTempUploadPath() . '/' . mt_rand(10000, 99999) . '-';
        
        if ($file instanceof UploadedFile)
        {
            $upload_path .= $file->getClientOriginalName();
            move_uploaded_file($file, $upload_path);
        }
        else
        {
            $upload_path .= basename($file);
            $this->getFilesystem()->copy($file, $upload_path);
        }
        
        return basename($upload_path);
    }
    
    /**
     * Removes a temporary file
     * 
     * @param string $file
     */
    public function removeTempFile($file)
    {
        $filesystem = $this->getFilesystem();
        $filesystem->remove($this->getAbsoluteTempUploadPath() . '/' . $file);
        
        return $this;
    }
    
    /**
     * Removes a file
     * 
     * @param string $file
     */
    public function removeFile($file)
    {
        $data = $this->getData();
        
        if (array_search($file, $data['removed_files']) === FALSE)
        {
            $data['removed_files'][] = $file;
        }
        
        $this->setData($data);
        
        return $this;
    }
    
    /**
     * Restore a temporary removed file
     * 
     * @param string $file
     * @return \Checkdomain\UploadManagerBundle\Service\UploadManager
     */
    public function restoreFile($file)
    {
        $data = $this->getData();
        
        if (($key = array_search($file, $data['removed_files'])) !== FALSE)
        {
            unset($data['removed_files'][$key]);
        }
        
        $this->setData($data);
        
        return $this;
    }
    
    /**
     * Get all files in the destination folder
     * 
     * @return array
     */
    public function getFiles()
    {
        $finder = $this->getFinder()
                       ->in($this->getAbsoluteUploadPath())
                       ->depth('< 1')
                       //->notContains('/^[\.]/')
                       ->files();
        
        $files = array();
        
        foreach ($finder AS $file)
        {
            $files[] = $file->getRelativePathname();
        }
        
        return $files;
    }
    
    /**
     * Get all files in the temporary folder
     * 
     * @return array
     */
    public function getTempFiles()
    {
        $finder = $this->getFinder()
                       ->in($this->getAbsoluteTempUploadPath())
                       ->depth('< 1')
                       ->notContains('/^[\.]/')
                       ->files();
        
        $files = array();
        
        foreach ($finder AS $file)
        {
            $files[] = $file->getRelativePathname();
        }
        
        return $files;
    }
    
    /**
     * Get all files as array by status
     * 
     * @return array
     */
    public function getFilesByStatus()
    {
        $data = $this->getData();

        $files = $this->getFiles();
        $added_files = $this->getTempFiles();
        $removed_files = $data['removed_files'];
        
        return array(
            self::FILE_STATUS_EXISTING => array_diff($files, $removed_files),
            self::FILE_STATUS_REMOVED => array_intersect($removed_files, $files),
            self::FILE_STATUS_ADDED => $added_files,
        );
    }
    
    /**
     * Synchronise the temporary directory and the destination folder
     */
    public function synchronise()
    {
        $files = $this->getFilesByStatus();
        $filesystem = $this->getFilesystem();
        
        foreach ($files[self::FILE_STATUS_REMOVED] AS $file)
        {
            $filesystem->remove($this->getAbsoluteUploadPath() . '/' . $file);
        }
        
        foreach ($files[self::FILE_STATUS_ADDED] AS $file)
        {
            $originFile = $this->getAbsoluteTempUploadPath() . '/' . $file;
            $targetFile = $this->getAbsoluteUploadPath() . '/' . $file;
            
            $filesystem->copy($originFile, $targetFile);
            
            // Remove temporary file
            $filesystem->remove($originFile);
        }
        
        // Clear deleted files
        $data = $this->getData();
        $data['removed_files'] = array();
        
        return $this;
    }
}