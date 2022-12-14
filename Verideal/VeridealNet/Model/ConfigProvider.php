<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\Model;

use Verideal\VeridealNet\Api\Data\VeridealConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements VeridealConfigInterface
{
    /** @var ScopeConfigInterface */
    private $scopeConfigInterface;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfigInterface = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function isVeridealApiEnabled($storeCode): bool
    {
        return $this->scopeConfigInterface->isSetFlag(
            self::XML_PATH_VERIDEAL_API_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * @inheritDoc
     */
    public function getVeridealDefaultToken($storeCode): ?string
    {
        return $this->scopeConfigInterface->getValue(
            self::XML_PATH_VERIDEAL_API_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
