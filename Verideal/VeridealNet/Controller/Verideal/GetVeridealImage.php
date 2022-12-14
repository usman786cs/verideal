<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\Controller\Verideal;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Verideal\VeridealNet\Api\VeridealApiInterface;
use Verideal\VeridealNet\Model\ConfigProvider;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;

class GetVeridealImage extends \Magento\Framework\App\Action\Action
{
    /** @var StoreManagerInterface */
    private $storeManagerInterface;

    /** @var VeridealApiInterface */
    private $veridealApiInterface;

    /** @var ConfigProvider */
    private $configProvider;

    /** @var LoggerInterface */
    private $loggerInterface;

    /** @var EncryptorInterface */
    private $encryptInterface;

    /**
     * @param StoreManagerInterface $storeManagerInterface
     * @param VeridealApiInterface $veridealApiInterface
     * @param LoggerInterface $loggerInterface
     * @param ConfigProvider $configProvider
     * @param EncryptorInterface $encryptInterface
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        VeridealApiInterface $veridealApiInterface,
        LoggerInterface $loggerInterface,
        ConfigProvider $configProvider,
        EncryptorInterface $encryptInterface,
        Context $context
    ) {
        parent::__construct($context);
        $this->storeManagerInterface = $storeManagerInterface;
        $this->veridealApiInterface = $veridealApiInterface;
        $this->loggerInterface = $loggerInterface;
        $this->configProvider = $configProvider;
        $this->encryptInterface = $encryptInterface;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $requestParams = $this->getRequest()->getParams();
        $token = isset($requestParams['token']) ? $requestParams['token'] : null;
        $pid = isset($requestParams['pid']) ? $requestParams['pid'] : null;
        if (!$token || !$pid) {
            $resultJson->setData(['success' => false]);
            return $resultJson;
        }

        $token = $this->encryptInterface->decrypt($token);

        if (!$this->configProvider->isVeridealApiEnabled($this->getStoreCode())) {
            $resultJson->setData(['success' => false]);
            return $resultJson;
        }

        try {
            $pid = intval($pid);
        } catch (\Exception $exception) {
        }

        //TODO: Comment out this section of code if required after client approval
        $inventory = $this->veridealApiInterface->requestQtyApi($pid, $token);
        if (!$inventory || $inventory <= 0) {
            $resultJson->setData(['success' => false]);
            return $resultJson;
        }

        $result = $this->veridealApiInterface->requestCodeApi($pid, $token);
        if (!$result) {
            $resultJson->setData(['success' => false]);
            return $resultJson;
        }

        try {
            $result = file_get_contents($result);
            $result = base64_encode($result);
        } catch (\Exception $exception) {
            $this->loggerInterface->error(
                'Verideal: Unable to get content of Image for PID: '
                . $pid
                . ' Error: ' . $exception->getMessage()
            );
            $resultJson->setData(['success' => false]);
            return $resultJson;
        }

        $resultJson->setData(['success' => true, 'verideal_image_url' => $result]);
        return $resultJson;

    }

    /**
     * @return string|null
     */
    private function getStoreCode(): ?string {
        try {
            return $this->storeManagerInterface->getStore()->getCode();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
