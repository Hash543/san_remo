<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api\Data;

interface PolicyInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const POLICY_VERSION = 'policy_version';
    const CONTENT = 'content';
    const LAST_EDITED_BY = 'last_edited_by';
    const COMMENT = 'comment';
    const STATUS = 'status';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getPolicyVersion();

    /**
     * @param string $policyVersion
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setPolicyVersion($policyVersion);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setContent($content);

    /**
     * @return int|null
     */
    public function getLastEditedBy();

    /**
     * @param int|null $lastEditedBy
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setLastEditedBy($lastEditedBy);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $comment
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setComment($comment);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     */
    public function setStatus($status);
}
