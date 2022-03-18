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
            'integrated' => $order->getIntegrated(),
            'invoiceNumber' => $order->getInvoiceNumber(),
            'tracking' => $order->getTracking(),
            'origin' => $order->getOrigin(),
            'service' => $order->getService(),
        ];
        return $this->insert($payload);
    }
}