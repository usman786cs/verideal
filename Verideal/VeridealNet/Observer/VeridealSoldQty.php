<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Verideal\VeridealNet\Api\VeridealApiInterface;
use Verideal\VeridealNet\Model\ConfigProvider;
use Psr\Log\LoggerInterface;

class VeridealSoldQty implements ObserverInterface
{
    /** @var VeridealApiInterface */
    private $veridealApiInterface;

    /** @var ConfigProvider */
    private $configProvider;

    /** @var LoggerInterface */
    private $loggerInterface;

    /**
     * @param VeridealApiInterface $veridealApiInterface
     * @param LoggerInterface $loggerInterface
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        VeridealApiInterface $veridealApiInterface,
        LoggerInterface $loggerInterface,
        ConfigProvider $configProvider
    ) {
        $this->veridealApiInterface = $veridealApiInterface;
        $this->loggerInterface = $loggerInterface;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getId()) {
            return;
        }

        try {
            $storeCode = $order->getStore()->getId();
        } catch (\Exception $exception) {
            $storeCode = null;
        }

        if (!$this->configProvider->isVeridealApiEnabled($storeCode)) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Item[] $orderItems */
        $orderItems = $order->getAllItems();

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $orderItem->getProduct();
            $pid = $product->getPid();
            if (!$pid) {
                continue;
            }

            try {
                $pid = intval($pid);
            } catch (\Exception $exception) {
                continue;
            }

            $token = $this->configProvider->getVeridealDefaultToken($storeCode);
            if (!$token) {
                continue;
            }
            $inventory = $this->veridealApiInterface->requestSoldApi($pid, $token, intval($orderItem->getQtyOrdered()));
            if (!$inventory || $inventory <= 0) {
                $this->loggerInterface->error(
                    __('Verideal: Inventory is Not Valid for Product' . $product->getId() . ' with Value: ' . $inventory)
                );
            }
        }
    }
}
