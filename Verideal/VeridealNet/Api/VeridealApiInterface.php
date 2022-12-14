<?php

declare(strict_types=1);

namespace Verideal\VeridealNet\Api;

interface VeridealApiInterface
{
    /**
     * @param int $pid
     * @param string $token
     * @return false|string
     */
    public function requestCodeApi(int $pid, string $token);

    /**
     * @param int $pid
     * @param string $token
     * @return null|int
     */
    public function requestQtyApi(int $pid, string $token): ?int;

    /**
     * @param int $pid
     * @param string $token
     * @return null|int
     */
    public function requestSoldApi(int $pid, string $token, int $qtyOrdered): ?int;
}
