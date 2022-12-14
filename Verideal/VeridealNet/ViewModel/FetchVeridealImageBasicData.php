<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Verideal\VeridealNet\Model\ConfigProvider;
use Magento\Framework\Encryption\EncryptorInterface;

class FetchVeridealImageBasicData implements ArgumentInterface
{
    /** @var StoreManagerInterface */
    private $storeManagerInterface;

    /** @var ConfigProvider */
    private $configProvider;

    /** @var EncryptorInterface */
    private $encryptInterface;

    /**
     * @param StoreManagerInterface $storeManagerInterface
     * @param ConfigProvider $configProvider
     * @param EncryptorInterface $encryptInterface
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        ConfigProvider $configProvider,
        EncryptorInterface $encryptInterface
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->configProvider = $configProvider;
        $this->encryptInterface = $encryptInterface;
    }

    /**
     * @return bool
     */
    public function isVeridealApiEnabled(): bool {
        $storeCode = $this->getStoreCode();
        return $this->configProvider->isVeridealApiEnabled($storeCode);
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

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     */
    public function getProductPid(\Magento\Catalog\Model\Product $product): ?int {
        if (!$product || !$product->getId()) {
            return null;
        }

        $pid = $product->getPid();
        if (!$pid) {
            return null;
        }
        try {
            return intval($pid);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string {
        $storeCode = $this->getStoreCode();
        return $this->encryptInterface->encrypt($this->configProvider->getVeridealDefaultToken($storeCode));
    }

    /**
     * @return string|null
     */
    public function getRequestUrl(): ?string {
        try {
            return $this->storeManagerInterface->getStore($this->getStoreCode())->getUrl('verideal/verideal/getveridealimage');
        } catch (\Exception $exception) {
            return null;
        }
    }
}
