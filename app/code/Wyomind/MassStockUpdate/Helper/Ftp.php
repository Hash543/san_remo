<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Helper;

class Ftp extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_ioFtp = null;
    protected $_ioSftp = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Io\Ftp $ioFtp,
        \Magento\Framework\Filesystem\Io\Sftp $ioSftp
    )
    {
        parent::__construct($context);
        $this->_ioFtp = $ioFtp;
        $this->_ioSftp = $ioSftp;
    }

    public function getConnection($data)
    {
        $port = $data['ftp_port'];
        $login = $data['ftp_login'];
        $password = $data['ftp_password'];
        $sftp = $data['use_sftp'];
        $active = $data['ftp_active'];

        $host = str_replace(["ftp://", "ftps://"], "", $data["ftp_host"]);
        if ($data['ftp_port'] != "" && $data["use_sftp"]) {
            $host .= ":" . $data['ftp_port'];
        }
        if (isset($data['file_path'])) {
            $fullFilePath = rtrim($data['ftp_dir'], "/") . "/" . ltrim($data['file_path'], "/");
            $fullPath = dirname($fullFilePath);
        } else {
            $fullPath = rtrim($data['ftp_dir'], "/");
        }


        if ($sftp) {
            $ftp = $this->_ioSftp;
        } else {
            $ftp = $this->_ioFtp;
        }
        $ftp->open(
                array(
                    'host' => $host,
                    'port' => $port,
                    'user' => $login, //ftp
                    'username' => $login, //sftp
                    'password' => $password,
                    'timeout' => '10',
                    'path' => $fullPath,
                    'passive' => !($active)
                )
        );

        // sftp doesn't chdir automatically when opening connection
        if ($sftp) {
            $ftp->cd($fullPath);
        }
        
        return $ftp;
    }
}
