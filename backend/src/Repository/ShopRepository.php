<?php
namespace Maisenvios\Middleware\Repository;

use Maisenvios\Middleware\Repository\BaseRepository;
use Maisenvios\Middleware\Model\Shop;

class ShopRepository extends BaseRepository {
    public function __construct()
    {
        parent::__construct('shop', Shop::class);
    }
}