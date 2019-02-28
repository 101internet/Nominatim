<?php

namespace Nominatim;

require_once(CONST_BasePath.'/lib/Cursor.php');

class Streets
{
    protected $oDB;
    protected $maxCount = CONST_Places_Max_ID_count;
    protected $count = 0;
    protected $cursor = null;
    protected $rows = [];

    public function __construct(&$oDB)
    {
        $this->oDB =& $oDB;
        $this->count = $this->maxCount;
    }

    /**
     * @return Record[]|null
     * @throws DatabaseError
     */
    public function get(){
        $order = 'ASC';
        $needReverse = false;
        $sSQL = 'SELECT osm_type, osm_id, place_id';
        $sSQL .= '  FROM placex';
        $sSQL .= '  WHERE rank_address = 26';
        $sSQL .= '  AND osm_type = \'W\'';
        $sSQL .= '  AND name IS NOT NULL';
        $sSQL .= '  AND type = \'residential\'';
        $sSQL .= '  AND country_code = \'in\'';
        if ($this->cursor instanceof Cursor){
            $fw = '>';
            if (!$this->cursor->forward){
                $fw = '<';
                $order = 'DESC';
                $needReverse = true;
            }
            $sSQL .= '  AND place_id '.$fw.' '.$this->cursor->sequentialId;

        }

        $sSQL .= '  ORDER BY place_id '.$order;
        $sSQL .= '  LIMIT '. $this->count;

        $rows = chksql(
            $this->oDB->getAll($sSQL),
            'Could not determine.'
        );
        $this->rows = $needReverse ? array_reverse($rows) : $rows;

        return $this->rows;
    }


    public function getCursors(){
        $cursors = ['next' => '', 'prev' => ''];


        if ($this->checkNext(end($this->rows))){
            $nextC = new Cursor(end($this->rows));
            $cursors['next'] = $nextC->toQueryString();
            reset($this->rows);
        }
        if ($this->checkPrev($this->rows[0])){
            $prevC = new Cursor($this->rows[0], false);
            $cursors['prev'] = $prevC->toQueryString();
        }

        return $cursors;
    }

    private function checkNext($record){
        $sSQL = 'SELECT osm_id';
        $sSQL .= '  FROM placex';
        $sSQL .= '  WHERE rank_address = 26';
        $sSQL .= '  AND place_id > ' . $record['place_id'];
        $sSQL .= '  AND osm_type = \'W\'';
        $sSQL .= '  AND name IS NOT NULL';
        $sSQL .= '  AND type = \'residential\'';
        $sSQL .= '  AND country_code = \'in\'';

        $sSQL .= '  ORDER BY place_id ASC';
        $sSQL .= '  LIMIT 1';

        $next = chksql(
            $this->oDB->getOne($sSQL),
            'Could not determine.'
        );

        return isset($next);
    }

    private function checkPrev($record){
        $sSQL = 'SELECT osm_id';
        $sSQL .= '  FROM placex';
        $sSQL .= '  WHERE rank_address = 26';
        $sSQL .= '  AND place_id < ' . $record['place_id'];
        $sSQL .= '  AND osm_type = \'W\'';
        $sSQL .= '  AND name IS NOT NULL';
        $sSQL .= '  AND type = \'residential\'';
        $sSQL .= '  AND country_code = \'in\'';

        $sSQL .= '  ORDER BY place_id ASC';
        $sSQL .= '  LIMIT 1';

        $prev = chksql(
            $this->oDB->getOne($sSQL),
            'Could not determine.'
        );

        return isset($prev);
    }

    public function loadParamArray($params){
        $this->cursor = new Cursor($params->getString('cursor'));

        $count = abs($params->getInt('count', $this->count));
        if ($this->count > $count){
            $this->count = $count;
        }
    }

}
