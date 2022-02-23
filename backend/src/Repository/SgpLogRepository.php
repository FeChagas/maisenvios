<?php
namespace Maisenvios\Middleware\Repository;

use Maisenvios\Middleware\Repository\BaseRepository;
use Maisenvios\Middleware\Model\SgpLog;

class SgpLogRepository extends BaseRepository {
    public function __construct()
    {
        parent::__construct('sgp_logs', SgpLog::class);
    }

    public function create(SgpLog $log) {
        $payload = [
            'shopId' => $log->getShopId(),
            'orderId' => $log->getOrderId(),
            'status_processamento' => $log->getStatus_processamento(),
            'status' => $log->getStatus(),
            'objetos' => $log->getObjetos(),
        ];
        return $this->insert($payload);
    }
}