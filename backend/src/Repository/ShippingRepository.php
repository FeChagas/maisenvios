<?php
namespace Maisenvios\Middleware\Repository;

use Maisenvios\Middleware\Repository\BaseRepository;
use Maisenvios\Middleware\Model\Shipping;

class ShippingRepository extends BaseRepository {
    public function __construct()
    {
        parent::__construct('shipping', Shipping::class);
    }
}