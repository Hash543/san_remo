<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Filesystem;

use Amasty\Feed\Api\Data\FeedInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class FeedOutput
 */
class FeedOutput
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var Compressor
     */
    private $compressor;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        Compressor $compressor
    ) {
        $this->filesystem = $filesystem;
        $this->compressor = $compressor;
    }

    public function get(\Amasty\Feed\Model\Feed $feed)
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $outputFilename = $filename = $feed->getFilename();

        if ($feed->getCompress()) {
            $outputFilename .= '.' . $feed->getCompress();
            if ($dir->isExist($filename)) {
                if ($dir->isExist($outputFilename)) {
                    $dir->delete($outputFilename);
                }

                try {
                    $this->compressor->pack(
                        $feed->getCompress(),
                        $dir->getAbsolutePath($filename),
                        $dir->getAbsolutePath($outputFilename)
                    );
                    $dir->delete($filename);
                } catch (LocalizedException $exception) {
                    $outputFilename = $filename;
                }
            }
        }

        return [
            'filename' => $outputFilename,
            'absolute_path' => $dir->getAbsolutePath($outputFilename),
            'content' => $dir->readFile($outputFilename),
            'mtime' => $dir->stat($outputFilename)['mtime']
        ];
    }

    public function delete(FeedInterface $feed)
    {
        $filename = $feed->getFilename();
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($dir->isFile($filename)) {
            $dir->delete($filename);
        }
    }
}
