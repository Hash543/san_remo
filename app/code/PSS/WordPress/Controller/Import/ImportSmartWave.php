<?php
/**
 * @author Israel Yasis
 */
namespace PSS\WordPress\Controller\Import;

use PSS\WordPress\Model\Context as WPContext;

/**
 * Class Index
 * @package Pss\ImporterBlog\Controller\Importer
 */
class ImportSmartWave extends \Magento\Framework\App\Action\Action
{
    /**
     * @var
     */
    protected $pageFactory;
    /**
     * @var \PSS\WordPress\Model\ResourceConnection
     */
    protected $connectionWordPress;
    /**
     * @var \PSS\WordPress\Model\Post
     */
    protected $post;
    /**
     * @var \PSS\WordPress\Model\ResourceModel\Post
     */
    protected $resourcePost;
    /**
     * @var \Magento\Framework\App\ResourceConnection\ConnectionFactory
     */
    protected $connectionFactory;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;
    /**
     * @var \PSS\WordPress\Model\PostFactory
     */
    protected $postFactory;

    /**
     * Test constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param WPContext $wpContext
     * @param \PSS\WordPress\Model\Post $post
     * @param \PSS\WordPress\Model\PostFactory $postFactory
     * @param \PSS\WordPress\Model\ResourceModel\Post $resourcePost
     * @param \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        WPContext $wpContext,
        \PSS\WordPress\Model\Post $post,
        \PSS\WordPress\Model\PostFactory $postFactory,
        \PSS\WordPress\Model\ResourceModel\Post $resourcePost,
        \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory
    ) {
        $this->connectionWordPress = $wpContext->getResourceConnection();
        $this->post = $post;
        $this->resourcePost = $resourcePost;
        $this->connectionFactory = $connectionFactory;
        $this->postFactory = $postFactory;
        return parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        return 'The importer is not working yet';
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connectionWordpress */
        $connectionWordpress = $this->connectionWordPress->getConnection();
        $selectPost = $this->getConnectionMagento1()->select()->from('smartwave_blog');
        $oldInformationPost = [];
        $errors = [];
        if ($results = $this->getConnectionMagento1()->fetchAll($selectPost)) {
            foreach ($results as $blog) {
                /** @var \PSS\WordPress\Model\Post $post */
                $post = $this->postFactory->create();
                $post->setData([
                    'post_author' => 1,
                    'post_title' => $blog['title'],
                    'post_content' => $blog['post_content'],
                    'post_status' => $this->getStatus((int)$blog['status']),
                    'post_date' => $blog['created_time'],
                    'post_date_gmt' => $blog['created_time'],
                    'post_modified_gmt' => $blog['update_time'],
                    'post_name' => $blog['identifier'],
                    'post_type' => 'post'
                ]);
                try {
                    $this->resourcePost->save($post);
                    $postId  = $post->getId();
                    $oldInformationPost[] = [
                        'old_id' => $blog['post_id'],
                        'new_id' => $postId
                    ];
                } catch (\Exception $exception) {
                    $postId = null;
                    $errors[] = 'Error importing post id: '. isset($blog['post_id'])? $blog['post_id'] : '';
                }
                if($postId && $blog['image']) {
                    $imagePath = $blog['image'];
                    $imagePath = pathinfo($imagePath);
                    if(is_array($imagePath) && isset($imagePath['filename'])) {
                        $selectImage = $connectionWordpress->select()->from($this->connectionWordPress->getTable('wordpress_post_meta'))
                            ->where('meta_key=?', '_wp_attached_file')->where('meta_value LIKE "%'.$imagePath['filename'].'%"');

                        if($results = $connectionWordpress->fetchRow($selectImage)) {

                            $connectionWordpress->insert($this->connectionWordPress->getTable('wordpress_post_meta'),
                                [
                                    'post_id' => $postId,
                                    'meta_key' => '_thumbnail_id',
                                    'meta_value' => $results['post_id'],
                                ]
                            );
                        }
                    }

                }
            }
        }
        $oldInformationCategory = [];
        $selectCategories = $this->getConnectionMagento1()->select()->from('smartwave_blog_cat');
        if($results = $this->getConnectionMagento1()->fetchAll($selectCategories)) {
            $tableNameCategory = $this->connectionWordPress->getTable('wordpress_menu');
            $tableNameTaxonomy = $this->connectionWordPress->getTable('wordpress_term_taxonomy');
            foreach ($results as $category) {

                $connectionWordpress->insert($tableNameCategory,
                    [
                        'name' => $category['title'],
                        'slug' => $category['identifier']
                    ]
                );
                $lastInsertId  = $connectionWordpress->lastInsertId($tableNameCategory);

                $selectCategoriesPosts = $this->getConnectionMagento1()->select()
                    ->from('smartwave_blog_post_cat',
                        "COUNT(smartwave_blog_post_cat.cat_id)")->where('cat_id=?', $category['cat_id']);
                $count = (int)$this->getConnectionMagento1()->fetchOne($selectCategoriesPosts);
                $connectionWordpress->insert($tableNameTaxonomy,
                    [
                        'term_id' => $lastInsertId,
                        'taxonomy' => 'category',
                        'parent' => 0,
                        'count' => $count
                    ]
                );
                $lastInsertIdTaxonomy  = $connectionWordpress->lastInsertId($tableNameTaxonomy);
                $oldInformationCategory[] = [
                    'old_id' => $category['cat_id'],
                    'new_id' => $lastInsertIdTaxonomy
                ];
            }
        }
        $selectCategoriesPosts = $this->getConnectionMagento1()->select()->from('smartwave_blog_post_cat');
        if($results = $this->getConnectionMagento1()->fetchAll($selectCategoriesPosts)) {
            $tableName = $this->connectionWordPress->getTable('wordpress_term_relationship');
            foreach ($results as $relationShip) {
                $catId = $this->getNewId($oldInformationCategory, $relationShip['cat_id']);
                $postId = $this->getNewId($oldInformationPost, $relationShip['post_id']);
                if($catId && $postId) {
                    $connectionWordpress->insert($tableName,
                        [
                            'object_id' => $postId,
                            'term_taxonomy_id' => $catId,
                            'term_order' => 0
                        ]
                    );
                }
            }
        }
    }

    /**
     * @param $name
     * @param $date
     * @return null|string
     */
    public function createUrl($name, $date) {
        $url = $name.$date;
        $url = preg_replace('~[^\pL\d]+~u', '-', $url);
        $url = iconv('utf-8', 'us-ascii//TRANSLIT', $url);
        $url = preg_replace('~[^-\w]+~', '', $url);
        $url = trim($url, '-');
        $url = preg_replace('~-+~', '-', $url);
        $url = strtolower($url);
        if (empty($url)) {
            return 'n-a';
        }
        return $url;
    }
    /**
     * @param array $ids
     * @param $id
     * @return null|int
     */
    public function getNewId(array $ids, $id) {
        foreach ($ids as $row) {
            if($row['old_id'] == $id) {
                return $row['new_id'];
            }
        }
        return null;
    }
    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnectionMagento1() {
        if($this->connection == null) {
            $this->connection = $this->connectionFactory->create([
                'host'     => '127.0.0.1',
                'dbname'   => 'san_remo_m1_05_04_2019',
                'username' => 'root',
                'password' => 'admin123',
                'active' => '1',
            ]);
        }
        return $this->connection;
    }

    /**
     * @param int $status
     * @return string
     */
    public function getStatus($status) {
        switch ($status) {

            case 1:
                $statusWordpress = 'publish';
                break;

            case 2:

                $statusWordpress = 'private';
                break;

            case 3:
                $statusWordpress = 'private';
                break;

            default:
                $statusWordpress = 'draft';
                break;
        }
        return $statusWordpress;
    }
}