<?php
namespace Maisenvios\Middleware\Repository;

use Maisenvios\Middleware\Repository\BaseRepository;
use Maisenvios\Middleware\Model\ShopMeta;

class ShopMetaRepository extends BaseRepository {
    public function __construct()
    {
        parent::__construct('shop_meta', ShopMeta::class);
    }

    public function create(ShopMeta $meta) {
        $payload = [
            'name' => $meta->getName(),
            'value' => $meta->getValue(),
            'shopId' => $meta->getShopId(),
        ];
        return $this->insert($payload);
    }
}