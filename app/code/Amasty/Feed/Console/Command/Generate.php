<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Console\Command;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Api\FeedRepositoryInterface;
use Amasty\Feed\Model\Config;
use Amasty\Feed\Model\Feed;
use Amasty\Feed\Model\FeedExport;
use Amasty\Feed\Model\Config\Source\ExecuteModeList;
use Amasty\Feed\Model\ValidProduct\ResourceModel\Collection as ValidProductsCollection;
use Amasty\Feed\Model\ValidProduct\ResourceModel\CollectionFactory as ValidProductsCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\UrlFactory;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console: feed:profile:generate
 *
 * @codingStandardsIgnoreFile
 */
class Generate extends AbstractSetupCommand
{
    /**
     * @var FeedRepositoryInterface
     */
    private $feedRepository;

    /**
     * @var ValidProductsCollectionFactory
     */
    private $vpCollectionFactory;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var State
     */
    private $state;

    /**
     * @var FeedExport
     */
    private $feedExport;

    public function __construct(
        FeedRepositoryInterface\Proxy $feedRepository,
        ValidProductsCollectionFactory $vpCollectionFactory,
        FeedExport $feedExport,
        UrlFactory $urlFactory,
        Config\Proxy $config,
        State $state,
        $name = null
    ) {
        parent::__construct($name);

        $this->feedRepository = $feedRepository;
        $this->vpCollectionFactory = $vpCollectionFactory;
        $this->urlFactory = $urlFactory;
        $this->config = $config;
        $this->state = $state;
        $this->feedExport = $feedExport;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('feed:profile:generate')
            ->setDescription('Generates feed for specified profile id');

        $this->setDefinition(
            [
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Feed profile ID.'
                )
            ]
        );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_GLOBAL,
            [$this, 'generate'],
            [$input, $output]
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function generate(InputInterface $input, OutputInterface $output)
    {
        try {
            $profileId = $input->getArgument('id');
            $itemsPerPage = (int)$this->config->getItemsPerPage();
            $totalGenerated = 0;
            $page = 1;
            $lastPage = false;

            /** @var FeedInterface $feed */
            $feed = $this->feedRepository->getById($profileId);

            /** @var ValidProductsCollection $vProductsCollection */
            $vProductsCollection = $this->vpCollectionFactory->create()
                ->setPageSize($itemsPerPage)->setCurPage($page);
            $vProductsCollection->addFieldToFilter(ValidProductsInterface::FEED_ID, $feed->getEntityId());

            $feed->setGenerationType(ExecuteModeList::MANUAL_GENERATED);
            $feed->setProductsAmount(0);

            $progressBar = $this->initProgressBar($output, $vProductsCollection->getSize());

            while ($page <= $vProductsCollection->getLastPageNumber()) {
                if ($page == $vProductsCollection->getLastPageNumber()) {
                    $lastPage = true;
                }

                $collectionData = $vProductsCollection->getData();
                $productIds = [];

                foreach ($collectionData as $datum) {
                    $productIds[] = $datum[ValidProductsInterface::VALID_PRODUCT_ID];
                }
                $currentBatch = count($productIds);

                $this->feedExport->export($feed, $page - 1, $productIds, $lastPage);
                $progressBar->advance($currentBatch);
                $totalGenerated += $currentBatch;
                $vProductsCollection->setCurPage(++$page)->resetData();
            }

            return $this->finish($output, $progressBar, $totalGenerated, $feed);
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($exception->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param OutputInterface $output
     * @param int $totalProductsSize
     *
     * @return ProgressBar
     */
    private function initProgressBar($output, $totalProductsSize)
    {
        $progressBar = new ProgressBar($output, $totalProductsSize);
        $progressBar->setFormat('<info>%message%</info> %current%/%max% [%bar%] %percent:3s%% %elapsed%');
        $progressBar->setMessage('Products processed:');
        $progressBar->start();

        return $progressBar;
    }

    /**
     * @param OutputInterface $output
     * @param ProgressBar $progressBar
     * @param int $totalGenerated
     * @param FeedInterface $feed
     *
     * @return int
     */
    private function finish($output, $progressBar, $totalGenerated, $feed)
    {
        $progressBar->finish();
        $output->writeln('');
        $output->writeln("<info>Total generated: $totalGenerated.</info>");
        $output->writeln("<comment>Download link: {$this->getDownloadLink($feed)}</comment>");

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @param FeedInterface $feed
     *
     * @return string
     */
    private function getDownloadLink($feed)
    {
        /** @var \Magento\Framework\UrlInterface $urlInstance */
        $urlInstance = $this->urlFactory->create();

        $routeParams = [
            '_direct' => 'amfeed/feed/download',
            '_query' => [
                'id' => $feed->getEntityId()
            ]
        ];

        return $urlInstance
                ->setScope($feed->getStoreId())
                ->getUrl('', $routeParams)
                //TODO outputFilename ????
            . '&file=' . $feed->getFilename();
    }
}
