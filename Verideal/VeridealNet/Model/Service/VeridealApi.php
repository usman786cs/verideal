<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\Model\Service;

use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class VeridealApi implements \Verideal\VeridealNet\Api\VeridealApiInterface
{
    const API_BASE_URL = 'https://api.verideal.net/v1/logocode/query?model=4';
    const API_ACT_CODE = 'code';
    const API_ACT_QTY = 'qty';
    const API_ACT_SOLD = 'sold';

    /** @var LoggerInterface */
    private $logger;

    /** @var Json */
    private $json;

    /**
     * @param LoggerInterface $logger
     * @param Json $json
     */
    public function __construct(
        LoggerInterface $logger,
        Json $json
    ) {
        $this->logger = $logger;
        $this->json = $json;
    }

    /**
     * @param int $pid
     * @param string $token
     * @return false|string
     */
    public function requestCodeApi(int $pid, string $token) {
        $result = $this->doRequest(self::API_ACT_CODE, $pid, $token, 0);
        if (!$result) {
            return false;
        }

        /**
         * Return URL of image to be visible on PDPs.
         */
        return self::API_BASE_URL . '&act=' . self::API_ACT_CODE . '&pid=' . $pid . '&token=' . $token;
    }

    /**
     * @param int $pid
     * @param string $token
     * @return int|null
     */
    public function requestQtyApi(int $pid, string $token): ?int {
        $result = $this->doRequest(self::API_ACT_QTY, $pid, $token, 0);
        return $this->decodeAndReturnResult($result, true);
    }

    /**
     * @param int $pid
     * @param string $token
     * @param int $qtyOrdered
     * @return int|null
     */
    public function requestSoldApi(int $pid, string $token, int $qtyOrdered): ?int {
        $inventory = $this->requestQtyApi($pid, $token);
        if (!$inventory || $inventory < 0) {
            return null;
        }

        $result = $this->doRequest(self::API_ACT_SOLD, $pid, $token, $qtyOrdered);
        return $this->decodeAndReturnResult($result);
    }

    /**
     * @param string $apiType
     * @param int $pid
     * @param string $token
     * @return bool|string
     */
    private function doRequest(string $apiType, int $pid, string $token, int $qtyOrdered = 0) {
        if ($qtyOrdered > 0) {
            $url = self::API_BASE_URL . '&act=' . $apiType . '&pid=' . $pid . '&amount=' . $qtyOrdered . '&token=' . $token;
        } else {
            $url = self::API_BASE_URL . '&act=' . $apiType . '&pid=' . $pid . '&token=' . $token;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        /**
         * Code API response is image and shouldn't be logged.
         */
        if (self::API_ACT_CODE !== $apiType) {
            $this->logResponse($response, $apiType);
        }

        if (200 === $info) {
            return $response;
        }
        return false;
    }

    /**
     * @param $result
     * @return mixed|null
     */
    private function decodeAndReturnResult($result, $isQtyResponse = false) {
        if (!$result) {
            return null;
        }

        $result = $this->json->unserialize($result);
        if (!isset($result['data'])) {
            return null;
        }

        if (!$isQtyResponse) {
            return $result['data'];
        }

        if (!isset($result['data']['inventory'])) {
            return null;
        }
        return $result['data']['inventory'];
    }

    /**
     * @param string $response
     * @param string $type
     */
    private function logResponse (string $response, string $type): void {
        $this->logger->info(__('Verideal:' . $type . 'API Response: ' . $response));
    }
}
