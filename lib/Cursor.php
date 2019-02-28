<?php

namespace Nominatim;

class Cursor {
    public $sequentialId = 0;
    public $forward = true;

    /**
     * Cursor constructor.
     * @param $record
     * @param $forward boolean
     */
    public function __construct($record, $forward = true)
    {
        if (!empty($record)) {
            if (is_string($record)) {
                $restored = $this->restore($record);
                $this->sequentialId = $restored->sequentialId ? $restored->sequentialId : 0;
                $this->forward = isset($restored->forward) ? $restored->forward : true;
            } else {
                $this->sequentialId = $record['place_id'];
                $this->forward = $forward;
            }
        }
    }

    public function toQueryString(){
        $aCursor = [
            'sequentialId' => $this->sequentialId,
            'forward' => $this->forward
        ];

        return base64_encode(json_encode($aCursor));
    }

    public function restore($str){
        return json_decode(base64_decode($str));
    }

    public function __toString()
    {
        return $this->toQueryString();
    }

}