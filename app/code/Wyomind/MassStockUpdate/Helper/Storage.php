<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Helper;

/**
 * Class Storage
 * @package Wyomind\MassStockUpdate\Helper
 */
class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{

    public $module = "MassStockUpdate";
    protected $_mageRootDir = null;
    protected $_driverFile = null;
    protected $_directoryList = null;
    protected $_driverFileFactory = null;
    protected $_driverHttpFactory = null;
    protected $_driverHttpsFactory = null;
    protected $_ioFtpFactory = null;
    protected $_ioSftpFactory = null;
    protected $_logger = null;

    /**
     * Storage constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory
     * @param \Wyomind\MassStockUpdate\Filesystem\Driver\HttpFactory $driverHttpFactory
     * @param \Wyomind\MassStockUpdate\Filesystem\Driver\HttpsFactory $driverHttpsFactory
     * @param \Magento\Framework\Filesystem\Io\FtpFactory $ioFtpFactory
     * @param \Magento\Framework\Filesystem\Io\SftpFactory $ioSftpFactory
     * @param \Wyomind\MassStockUpdate\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory,
        \Wyomind\MassStockUpdate\Filesystem\Driver\HttpFactory $driverHttpFactory,
        \Wyomind\MassStockUpdate\Filesystem\Driver\HttpsFactory $driverHttpsFactory,
        \Magento\Framework\Filesystem\Io\FtpFactory $ioFtpFactory,
        \Magento\Framework\Filesystem\Io\SftpFactory $ioSftpFactory,
        \Wyomind\MassStockUpdate\Logger\Logger $logger
    )
    {
        parent::__construct($context);
        $this->_directoryList = $directoryList;
        $this->_driverFileFactory = $driverFileFactory;
        $this->_driverHttpFactory = $driverHttpFactory;
        $this->_driverHttpsFactory = $driverHttpsFactory;
        $this->_ioFtpFactory = $ioFtpFactory;
        $this->_ioSftpFactory = $ioSftpFactory;
        $this->_logger = $logger;
    }

    public function newTempFileName()
    {
        $class = "\Wyomind\\" . $this->module . "\Helper\Data";
        $tmpFolder = $this->getMageRootDir() . $class::TMP_FOLDER;
        $this->mkdir($tmpFolder);
        $tempFileName = tempnam($tmpFolder, $class::TMP_FILE_PREFIX) . '.' . $class::TMP_FILE_EXT;
        if (strpos($tempFileName, ".orig") !== false) {
            $this->deleteFile(dirname($tempFileName), basename($tempFileName));
            $tempFileName = str_replace(".orig", "", $tempFileName);
        }

        return $tempFileName;
    }

    /**
     * Evaluate the file path as a the regular expression
     * @param $path
     * @param $fileType
     * @param bool $isMultiple
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function evalRegexp($path, $fileType, $isMultiple = false)
    {
        if ($fileType != Data::LOCATION_MAGENTO) {
            if (!$isMultiple) {
                return $path;
            }
            return array($path);
        }

        $driverFile = $this->getDriverFile();
        $path = rtrim($this->getMageRootDir(), "/") . "/" . ltrim($path, "/");
        $directory = substr($path, 0, strrpos($path, "/"));

        $fileInDir = $driverFile->readDirectory($directory);
        $files = array();
        foreach ($fileInDir as $file) {
            $pattern = str_replace(array("/", ".", "(", ")"), array("\/", "\.", "\(", "\)"), $path);
            if (preg_match("#" . $pattern . "#", $file)) {

                $files[] = str_replace(rtrim($this->getMageRootDir(), "/"), "", $file);
            }
        }

        if (count($files)) {
            if (!$isMultiple) {
                return $files[0];
            } else {
                return $files;
            }
        }

        return str_replace(rtrim($this->getMageRootDir(), "/"), "", $path);
    }

    /**
     * Retrieve the content of the import file and put it in a tmp file
     * @param array $params
     * @return mixed|string the temp file
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImportFile($params)
    {
        try {
            $class = "\Wyomind\\" . $this->module . "\Helper\Data";
            $this->_logger->notice(">> " . __METHOD__ . "()");
            $this->_logger->notice(__("Retrieving file containing data to update"));

            $tmpFileName = $this->newTempFileName();

            $driverFile = $this->getDriverFile();

            switch ($params["file_system_type"]) {
                case $class::LOCATION_MAGENTO:
                    $this->_logger->notice(__("Retrieving file from Magento file system"));
                    $newFileName = rtrim($this->getMageRootDir(), "/") . "/" . ltrim($params['file_path'], "/");
                    $this->_logger->notice(__("Retrieving file : " . $newFileName));
                    if (!$driverFile->isExists($newFileName)) {
                        $this->_logger->notice(__("Magento File System : File %1 not found !", $newFileName));
                        throw new \Exception(__("Magento File System : File %1 not found !", $newFileName));
                    } else {
                        $driverFile->copy($newFileName, $tmpFileName);
                    }
                    break;

                case $class::LOCATION_URL:
                    $this->_logger->notice(__("Retrieving file from url"));
                    $content = "";
                    if (strpos($params["file_path"], "http://") !== false) { // HTTP
                        $url = str_replace("http://", "", $params["file_path"]);
                        $this->_logger->notice("Url : " . $url);
                        $driverHttp = $this->_driverHttpFactory->create();
                        if (!$driverHttp->isExists($url)) {
                            $this->_logger->notice(__("HTTP : File %1 not found ! (%2)", $params["file_path"], $driverHttp->getStatus()));
                            throw new \Exception(__("HTTP : File %1 not found ! (%2)", $params["file_path"], $driverHttp->getStatus()));
                        }
                        $content = $driverHttp->fileGetContents($url);
                    } elseif (strpos($params["file_path"], "https://") !== false) { // HTTPS
                        $url = str_replace("https://", "", $params["file_path"]);
                        $this->_logger->notice("Url : " . $url);
                        $driverHttps = $this->_driverHttpsFactory->create();
                        if (!$driverHttps->isExists($url)) {
                            $this->_logger->notice(__("HTTPS : File %1 not found ! (%2)", $params["file_path"], $driverHttps->getStatus()));
                            throw new \Exception(__("HTTPS : File %1 not found ! (%2)", $params["file_path"], $driverHttps->getStatus()));
                        }
                        $content = $driverHttps->fileGetContents($url);
                    }

                    $driverFile->filePutContents($tmpFileName, $content);
                    break;

                case $class::LOCATION_FTP:
                    $this->_logger->notice(__("Retrieving file from FTP server"));
                    if ($params["use_sftp"]) {
                        $ftp = $this->_ioSftpFactory->create();
                    } else {
                        $ftp = $this->_ioFtpFactory->create();
                    }

                    $host = str_replace(["ftp://", "ftps://"], "", $params["ftp_host"]);
                    if ($params['ftp_port'] != "" && $params["use_sftp"]) {
                        $host .= ":" . $params['ftp_port'];
                    }

                    $fullFilePath = rtrim($params['ftp_dir'], "/") . "/" . ltrim($params['file_path'], "/");
                    $fullPath = dirname($fullFilePath);
                    $fileName = basename($fullFilePath);

                    $ftp->open([
                        'host' => $host,
                        'user' => $params["ftp_login"],
                        // sftp only
                        'username' => $params["ftp_login"],
                        'port' => $params['ftp_port'],
                        'password' => $params["ftp_password"],
                        'timeout' => '120',
                        'path' => $fullPath,
                        'passive' => !$params["ftp_active"]
                    ]);
                    $ftp->cd($fullPath);
                    $allFiles = $ftp->ls();
                    $found = false;

                    foreach ($allFiles as $file) {

                        if (str_replace(["//","/./"], "/", $file['id']) == $fullFilePath) {
                            $found = true;
                        }
                    }
                    if ($found) {
                        $ftp->read($fileName, $tmpFileName);
                        $ftp->close();
                    } else {
                        $ftp->close();
                        $this->_logger->notice(__("FTP : File %1 not found !", $fullFilePath));
                        throw new \Exception(__("FTP : File %1 not found !", $fullFilePath));
                    }

                    break;
                case $class::LOCATION_DROPBOX:
                    $this->_logger->notice(__("Retrieving file from dropbox"));
                    $path = $params['file_path'];
                    $token = $params['dropbox_token'];
                    try {
                        $url = "https://content.dropboxapi.com/2/files/download";
                        $headers = array(
                            'Authorization: Bearer ' . $token,
                            'Content-Type: text/plain',
                            'Dropbox-API-Arg: ' . json_encode(array('path' => $path))
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        curl_close($ch);

                        if (strstr(str_replace(array("\n", "\r", "\t"), '', $content), 'invalid_access_token')) {
                            throw new \Exception($this->__("The token is invalid. It can be generated from your Dropbox account https://www.dropbox.com/developers/apps"));
                        }

                        if ($content === false) {
                            throw new \Exception($this->__("The file '" . $params['file_path'] . "' seems to be empty."));
                        }

                        if (!$content) {
                            throw new \Exception($this->__("The file '" . $params['file_path'] . "' cannot be fetched."));
                        }
                    } catch (\Exception $er) {
                        $this->_logger->notice(__("DROPBOX : %1", $er->getMessage()));
                        throw new \Exception(__("DROPBOX : %1", $er->getMessage()));
                    }
                    $driverFile->filePutContents($tmpFileName, $content);
                    break;
                case $class::LOCATION_WEBSERVICE:
                    $this->_logger->notice(__("Retrieving file from webservice"));
                    $url = $params['file_path'];
                    $data = str_replace("{{DATE}}", date('Y-m-d'), $params['webservice_params']);
                    $login = $params['webservice_login'];
                    $password = $params['webservice_password'];
                    $content = "";
                    try {
                        $fields = array(
                            'login' => urlencode($login),
                            'password' => urlencode($password),
                            'data' => base64_encode(gzencode($data))
                        );
                        $fields_string = "";
                        foreach ($fields as $key => $value) {
                            $fields_string .= $key . '=' . $value . '&';
                        }
                        rtrim($fields_string, '&');
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, count($fields));
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($ch);
                        curl_close($ch);
                    } catch (\Exception $er) {
                        $this->_logger->notice(__("WEB SERVICE : %1", $er->getMessage()));
                        throw new \Exception(__("WEB SERVICE : %1", $er->getMessage()));
                    }
                    $driverFile->filePutContents($tmpFileName, $content);

                    break;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Source file `%1` doesn't exist (%2). Please check the source file path. ", $params['file_path'], $e->getMessage()));
        }

        $this->_logger->notice(__("Temporary file created : %1", $tmpFileName));
        return $tmpFileName;
    }

    /**
     * Create a folder
     * @param string $folder
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function mkdir($folder)
    {
        $driverFile = $this->getDriverFile();
        $driverFile->createDirectory($folder, 0755);
    }

    /**
     * Delete a file
     * @param string $folder
     * @param string $file
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteFile($folder, $file)
    {
        $driverFile = $this->getDriverFile();

        if ($driverFile->isExists($folder . "/" . $file)) {
            $driverFile->deleteFile($folder . '/' . $file);
        }
    }

    public function moveFile($folderFrom, $fileFrom, $folderTo, $fileTo)
    {
        $driverFile = $this->getDriverFile();
        if ($driverFile->isExists($folderFrom . "/" . $fileFrom)) {
            $this->mkdir($folderTo);
            $driverFile->rename($folderFrom . DIRECTORY_SEPARATOR . $fileFrom, $folderTo . DIRECTORY_SEPARATOR . $fileTo);
        }
    }

    /**
     * Open file
     * @param string $folder
     * @param string $file
     * @param string $mode
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function fileOpen($folder, $file, $mode = 'w')
    {
        $driverFile = $this->getDriverFile();
        $resource = $driverFile->fileOpen($folder . "/" . $file, $mode);
        return $resource;
    }

    public function fileClose($resource)
    {
        $driverFile = $this->getDriverFile();
        $result = $driverFile->fileClose($resource);
        return $result;
    }

    public function fileWrite($resource, $data)
    {
        $driverFile = $this->getDriverFile();
        $result = $driverFile->fileWrite($resource, $data);
        return $result;
    }

    public function fileReadLine($resource)
    {
        $driverFile = $this->getDriverFile();
        $result = $driverFile->fileReadLine($resource, 1024000, "\n");
        return $result;
    }

    /**
     * Put data in a csv file
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function filePutCsv($resource, $data, $delimiter = ",", $enclosure = "")
    {
        if ($enclosure == "none") {
            $enclosure = "";
        }
        $driverFile = $this->getDriverFile();
        if ($enclosure == "") {
            $driverFile->filePutCsv($resource, $data, $delimiter);
        } else {
            $driverFile->filePutCsv($resource, $data, $delimiter, $enclosure);
        }
    }

    /**
     * Get Mage root dir
     */
    public function getMageRootDir()
    {
        if ($this->_mageRootDir == null) {
            $this->_mageRootDir = $this->_directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        }
        return $this->_mageRootDir;
    }

    /**
     * Get file driver instance
     * @return null|\Magento\Framework\Filesystem\Driver\File
     */
    public function getDriverFile()
    {
        if ($this->_driverFile == null) {
            $this->_driverFile = $this->_driverFileFactory->create();
        }
        return $this->_driverFile;
    }

}
