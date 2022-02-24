<?php
namespace Maisenvios\Middleware\Repository;

use Maisenvios\Middleware\Repository\BaseRepository;
use Maisenvios\Middleware\Model\Shop;

class ShopRepository extends BaseRepository {
    public function __construct()
    {
        parent::__construct('shop', Shop::class);
    }

    public function findNextToRun() {
        $where = ['active' => 1];
        $join = [ 'sgp_logs' => [ 'parentKey' => 'id', 'childKey' => 'shopId','where' => []]];
        $select = ['shop.id', 'MAX(sgp_logs.createdAt) AS `lastRunAt`'];
        $groupBy = ['shop.id'];
        $shops = $this->findAll($where, 0, [], $join, $select, $groupBy, true);
        $nextToRunId = 0;
        $nextToRunLastRunAt = new \DateTime();
        foreach ($shops as $key => $shop) {
            $date = new \DateTime($shop['lastRunAt']);
            if ($nextToRunLastRunAt > $date) {
                $nextToRunId = $shop['id'];
                $nextToRunLastRunAt = $date;
            }
        }
        return $this->findOneBy(['id' => $nextToRunId]);
    }
}