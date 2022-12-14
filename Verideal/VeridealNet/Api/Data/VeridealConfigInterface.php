<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\Api\Data;

interface VeridealConfigInterface
{
    const XML_PATH_VERIDEAL_API_ENABLED = 'verideal/general/enable';
    const XML_PATH_VERIDEAL_API_TOKEN = 'verideal/general/token';

    /**
     * @param $storeCode
     * @return bool
     */
    public function isVeridealApiEnabled($storeCode): bool;

    /**
     * @param $storeCode
     * @return string|null
     */
    public function getVeridealDefaultToken($storeCode): ?string;
}
