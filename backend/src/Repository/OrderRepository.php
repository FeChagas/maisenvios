<?php
namespace Maisenvios\Middleware\Repository;

use Maisenvios\Middleware\Repository\BaseRepository;
use Maisenvios\Middleware\Model\Order;

class OrderRepository extends BaseRepository {
    public function __construct()
    {
        parent::__construct('orders', Order::class);
    }

    public function create(Order $order) {
        $payload = [
            'orderId' => $order->getOrderId(),
            'storeId' => $order->getStoreId(),
            'integrated' => $order->getIntegrated()
        ];
        return $this->insert($payload);
    }
}