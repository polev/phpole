<?php namespace Polev\Phpole\Database;

class DatabaseTs extends Database
{
    private $getTrashed = 0; // -1:onlyTrashed, 0:withOutTrashed, 1:withTrashed

    private function getQueryByTrashStatus($query)
    {
        if ($this->getTrashed < 0) {
            $query['rmts'] = ['$gt' => 0];
        } elseif ($this->getTrashed < 1) {
            $query['rmts'] = 0;
        }
        $this->withOutTrashed();
        return $query;
    }

    function all($query = [], $fields = [], $sort = null, $limit = null, $skip = null)
    {
        $query = $this->getQueryByTrashStatus($query);
        return parent::all($query, $fields, $sort, $limit, $skip);
    }

    function count($query = [])
    {
        $query = $this->getQueryByTrashStatus($query);
        return parent::count($query);
    }

    function page($query = [], $fields = [], $sort = null, $page = 1, $pagesize = 50)
    {
        $query = $this->getQueryByTrashStatus($query);
        if ($page < 1) $page = 1;
        if ($pagesize < 1) $pagesize = 50;
        $skip = ($page - 1) * $pagesize;
        $total = parent::count($query);
        $list = parent::all($query, $fields, $sort, $pagesize, $skip);
        $pagemax = ceil($total / $pagesize);
        return [$list, compact('total', 'page', 'pagesize', 'pagemax')];
    }

    function insert($a)
    {
        if ( ! isset($a['crts'])) $a['crts'] = $_SERVER['REQUEST_TIME'];
        if ( ! isset($a['upts'])) $a['upts'] = $_SERVER['REQUEST_TIME'];
        return parent::insert($a);
    }

    function update($criteria, $new_object, $options = [])
    {
        if ( ! isset($new_object['rmts']) &&  ! isset($new_object['upts'])) {
            $new_object['upts'] = $_SERVER['REQUEST_TIME'];
        }
        return parent::update($criteria, $new_object, $options);
    }

    function softDelete($criteria)
    {
        return $this->update($criteria, ['rmts' => $_SERVER['REQUEST_TIME']]);
    }

    function restore($criteria)
    {
        return $this->update($criteria, ['rmts' => 0]);
    }

    function onlyTrashed()
    {
        $this->getTrashed = -1;
        return $this;
    }

    function withOutTrashed()
    {
        $this->getTrashed = 0;
        return $this;
    }

    function withTrashed()
    {
        $this->getTrashed = 1;
        return $this;
    }
}