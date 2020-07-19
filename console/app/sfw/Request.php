<?php
namespace SFW;

class Request implements \JsonSerializable,\ArrayAccess {
    public function __construct(Array $data,Array $files,Array $headers) {
        $this->data = $data;
        $this->files = $files;
        $this->headers = $headers;
        
    }

    public function jsonSerialize() {
        return ['data'=>$this->data,'headers'=>$this->headers,'files'=>$this->files];
    }

    public function offsetSet($offset, $value) {
        if (!is_null($offset)) {
            $this->$offset = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetUnset($offset) {
        unset($this->$offset);
    }

    public function offsetGet($offset) {
        return isset($this->$offset) ? $this->$offset : null;
    }
}
