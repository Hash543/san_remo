<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Policy;

use Amasty\Gdpr\Controller\Adminhtml\AbstractPolicy;
use Amasty\Gdpr\Api\Data\PolicyInterface;
use Amasty\Gdpr\Api\PolicyRepositoryInterface;
use Amasty\Gdpr\Model\ResourceModel\Policy;

class InlineEdit extends AbstractPolicy
{
    /**
     * @var PolicyRepositoryInterface
     */
    private $repository;

    /**
     * @var Policy
     */
    private $policy;

    public function __construct(
        PolicyRepositoryInterface $repository,
        Policy $policy,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->policy = $policy;
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach ($postItems as $policyId => $policyData) {
            /** @var \Amasty\Gdpr\Model\Policy $policy */
            $policy = $this->repository->getById($policyId);
            try {
                if ($policyData['status'] == \Amasty\Gdpr\Model\Policy::STATUS_DISABLED) {
                    $messages[] = 'Sorry, you can\'t disable the active Privacy Policy without enabling another one. '
                     . 'Please enable another version of Privacy Policy. The current active version will be disabled '
                     . 'automatically.';
                    $error = true;
                } elseif(!$policy->getData('policy_version')) {
                    $messages[] = 'Sorry, you can\'t change status of policy which has no version.';
                    $error = true;
                } else {
                    $policy->setStatus((int)$policyData['status']);
                    $this->repository->save($policy);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPolicy($policy, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPolicy($policy, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPolicy(
                    $policy,
                    __('Something went wrong while saving the question.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    private function getErrorWithPolicy(PolicyInterface $policy, $errorText)
    {
        return '[Policy ID: ' . $policy->getId() . '] ' . $errorText;
    }
}
