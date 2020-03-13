<?php

namespace Pss\CsvProcessor\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;

class CsvGenerator extends AbstractHelper
{
    const CSV_INPUT_CONFIG_PATH = 'csv_processor/general/input_csv';

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvProcessor;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var array
     */
    private $csvData;
    /**
     * @var array
     */
    private $csvNewData;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $writer;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * GenerateCsv constructor.
     * @param Context $context
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param ProductFactory $productFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     */
    public function __construct(
        Context $context,
        \Magento\Framework\File\Csv $csvProcessor,
        ProductFactory $productFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\Config\Storage\WriterInterface $writer,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        parent::__construct($context);
        $this->ioFile = $ioFile;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->csvData = [];
        $this->csvNewData = [];
        $this->file = $file;
        $this->writer = $writer;
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function reGenerateCsv()
    {
        $inputCsv = $this->scopeConfig->getValue(self::CSV_INPUT_CONFIG_PATH);
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            . "/csv_processor/{$inputCsv}";
        if (!isset($filePath)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }

        $delimiter = $this->scopeConfig->getValue('csv_processor/general/delimiter');
        $enclosure = $this->scopeConfig->getValue('csv_processor/general/enclosure');

        $this->csvProcessor->setDelimiter($delimiter);
        $this->csvProcessor->setEnclosure($enclosure);

        $rows = $this->csvProcessor->getData($filePath);
        if(count($rows) == 0 ) {
            throw new LocalizedException(_("Invalid File"));
        }
        $header = array_shift($rows);
        $header = array_map('strtolower', $header);
        $this->csvData = $this->dataSort($header, $rows);

        // Add BINDS/ROWS
        foreach ($this->csvData as $index => $data) {
            // Is repeated
            $skip = false;
            foreach ($this->csvNewData as $newData) {
                if ($data['sku'] == $newData['sku']) {
                    $skip = true;
                }
            }
            if ($skip) continue;

            // Start filtering
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect('*')
                ->addAttributeToFilter('gama', $data['gama'])
                ->addAttributeToFilter('type_id', 'configurable')
                ->setPageSize(1);

            if ($collection->count() == 0) {
                /*
                 * Existe producto simple con mismo valor de gam‌a, pero diferente valor de volumen umen?
                 */
                $this->searchSameGama($this->csvData, $data['gama'], $index);
                continue;
            } else {
                /*
                 * Si existe configurable con la misma gamma informamos el parent_sku con el sku del configurable.
                 */
                $productConf = $collection->getData()[0];
                $bind = $this->buildBind($data, 'simple', $productConf['sku']);
                array_push($this->csvNewData, $bind);
                unset($this->csvData[$index]); // Quitamos el simple
            }
        }

        // Add HEADED
        if (isset($this->csvNewData[0])) {
            $header = array_keys($this->csvNewData[0]);
        }
        $this->csvNewData = $this->uniqueMultidimArray($this->csvNewData, 'sku');
        array_unshift($this->csvNewData, $header);

        $outFile = $this->scopeConfig->getValue('csv_processor/general/output_dir');

        $newFilePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)
            . "/{$outFile}";
        $pathDirectory = explode('/', $newFilePath);
        unset($pathDirectory[count($pathDirectory)-1]);
        $pathDirectory = implode('/', $pathDirectory);
        if (!is_dir($pathDirectory )){
            $this->ioFile->mkdir($pathDirectory, 0775);
        }

        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData(
                $newFilePath,
                $this->csvNewData
            );

        if ($this->file->isExists($filePath))  {
            $this->file->deleteFile($filePath);
            $this->writer->delete(self::CSV_INPUT_CONFIG_PATH);
        }
    }

    // Por defecto todos son simples
    private function buildBind($bind, $type = 'simple', $parenSku = '')
    {
        // Type
        $bind['type'] = $type;

        // AtributeSet
        $attributeSet = 'Varios';
        if (isset($bind['familia'])) {
            $fam = str_split($bind['familia']);
            if ($fam[0] == '0') {
                $attributeSet = 'Cosmeticos';
            } elseif ($fam[0] == '1' && count($fam) >= 5) {
                if ($fam[5] == '1') {
                    $attributeSet = 'Cosmeticos';
                } elseif ($fam[5] == '2') {
                    $attributeSet = 'Maquillajes';
                } elseif ($fam[5] == '3') {
                    $attributeSet = 'Perfumes';
                }
            }
        }
        $bind['attribute_set'] = $attributeSet;

        // ParentSku
        $bind['parent_sku'] = $parenSku;

        // Visibility
        $visibility = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE;
        if ($type == 'configurable') {
            $visibility = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH;
        } elseif ($type == 'simple' && empty($parenSku)) {
            $visibility = \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH;
        }
        $bind['visibility'] = $visibility;

        //NL2BR description
        $bind['description'] = nl2br($bind['description']);

        // ConfigAttribute
        $bind['config_attribute'] = 'volumen';
        if ($type == 'configurable') {
            $bind['name'] = str_ireplace($bind['volumen'], '', $bind['name']);
            //QUITAMOS EL SKU DEL NOMBRE SI ES CONFIGURABLE
            $bind['url'] = $this->sluggify($bind['name']); //.'-'.$this->sluggify($bind['sku']) ;
        } else {
            $bind['url'] = $this->sluggify($bind['name']).'-'.$this->sluggify($bind['sku']) ;
        }
        return $bind;
    }

    private function dataSort($header, $rows)
    {
        $array = [];
        foreach ($rows as $row) {
            if ($row > 0) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $array [] = $data;
            }
        }
        return $array;
    }

    private function searchSameGama($data, $gama, $index)
    {
        $push = $data[$index];
        $flag = false;
        $simpleGroup = [];
        array_push($simpleGroup, $push);
        unset($data[$index]);
        foreach ($data as $key => $value) {
            if ($gama == $value['gama']) {
                array_push($simpleGroup, $value);
                unset($data[$key]);
                unset($this->csvData[$index]);
                unset($this->csvData[$key]);
                $flag = true;
            }
        }

        if ($flag == false) {
            $bind = $this->buildBind($push, 'simple'); // Simple with no parent
            array_push($this->csvNewData, $bind);
        } else {
            $this->createConfig($simpleGroup);
        }
    }

    private function createConfig($simpleGroup)
    {

        // Config Product Setting
        $prefix = $this->scopeConfig->getValue('csv_processor/general/conf_prefix');
        $prefix = is_string($prefix) ? $prefix : '';
        $sufix = $this->scopeConfig->getValue('csv_processor/general/conf_sufix');
        $sufix = is_string($sufix) ? $sufix : '';

        $cProduct = [];
        $volumenCode = 'volumen';
        $volumen = array_column($simpleGroup, $volumenCode, 'sku');
        foreach ($volumen as $key => $data) {
            foreach ($simpleGroup as $simpleG) {
                if ($simpleG['sku'] == $key) {
                    $parentSku = $simpleGroup[0]['sku'] . 'CONF';
                    $bind = $this->buildBind($simpleG, 'simple', $parentSku); // has parent, is simple
                    array_push($cProduct, $bind);
                }
            }
        }


        $configurable = $cProduct[0];

        $configurable['sku'] = $configurable['sku'] . 'CONF';
        $configurable['ean'] = $configurable['ean'] . '_conf';

        //PSS: QUITAMOS EL SUFFIX CONF DEL NOMBRE DEL PRODUCTO
        if($simpleGroup[0]['volumen']!='') {
            $nn = trim(str_replace($configurable['volumen'], '', $configurable['name']));
            $configurable['name'] = $prefix . $nn; // . $sufix;
        }else{
            $configurable['name'] = $prefix . $configurable['name']; // . $sufix;
        }

        $bind = $this->buildBind($configurable, 'configurable'); // Configurable

        array_unshift($cProduct, $bind); // Add bind to biginning

        $this->csvNewData = array_unique(array_merge($this->csvNewData, $cProduct), SORT_REGULAR);
    }

    /**
     * @param $url
     * @return mixed|null|string|string[]
     */
    private function sluggify($url) {
        $url = strtolower($url);
        $url = strip_tags($url);
        $url = stripslashes($url);
        $url = html_entity_decode($url);

        $url = str_replace('\'', '', $url);
        $match = '/[^a-z0-9]+/';
        $replace = '-';
        $url = preg_replace($match, $replace, $url);
        $url = trim($url, '-');
        return $url;
    }
    /**
     * Remove duplicate from CSV
     * @param $array
     * @param $key
     * @return array
     */
    private function uniqueMultidimArray($array, $key) {
        $tempArray = [];
        $i = 0;
        $keyArray = [];

        foreach($array as $val) {
            if (!in_array($val[$key], $keyArray)) {
                $keyArray[$i] = $val[$key];
                $tempArray[$i] = $val;
            }
            $i++;
        }
        return $tempArray;
    }
}
