<?php

namespace App\Model;

use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Utils\FileSystem;
use Nette\Utils\Image;
use Nette\Utils\Random;
use Nette\Utils\Strings;

class PartnerLogoStorage
{
    /**
     * @var string
     */
    private $uploadDir;
    /**
     * @var string
     */
    private $urlPrefix;

    /**
     * @var Request
     */
    private $request;


    /**
     * PartnerLogoStorage constructor.
     * @param string $uploadDir
     * @param string $urlPrefix
     * @param Request $request
     */
    public function __construct($uploadDir, $urlPrefix, Request $request)
    {
        $this->uploadDir = $uploadDir;
        $this->urlPrefix = $urlPrefix;
        $this->request = $request;
    }


    /**
     * @param Image $image
     * @param string|null $name
     * @return string
     */
    public function saveImage(Image $image, $name = null)
    {
        $filename = $this->getFilename($name);

        $storageFile = $this->getStorageFilename($filename);

        $image->save($storageFile);

        return $this->getUrl($filename);
    }


    /**
     * @param FileUpload $file
     * @param string|null $name
     * @return string
     */
    public function saveUploaded(FileUpload $file, $name = null)
    {
        $filename = $this->getFilename($name, $this->getExtension($file->name));

        $storageFile = $this->getStorageFilename($filename);

        $file->move($storageFile);

        return $this->getUrl($filename);
    }


    /**
     * @param $name
     * @return string
     */
    private function getExtension($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }

    /**
     * @param string|null $name
     * @param string $ext
     * @return string
     */
    private function getFilename($name = null, $ext = 'png')
    {
        if ($name) {
            $name = Strings::webalize($name) . '-';
        }
        return $name . Random::generate(5) . '.' . $ext;
    }


    /**
     * @param string $filename
     * @return string
     */
    private function getStorageFilename($filename)
    {
        $uploadDir = $this->uploadDir;
        FileSystem::createDir($uploadDir);
        return $uploadDir . '/' . $filename;
    }


    /**
     * @param string $filename
     * @return string
     */
    private function getUrl($filename)
    {
        $baseUrl = $this->request->getUrl()->getBaseUrl();
        return $baseUrl . $this->urlPrefix . '/' . $filename;
    }
}
