<?php
namespace SFW;

class Request implements \JsonSerializable,\ArrayAccess {
    public function __construct(Array $data,Array $files,Array $headers,?Route $route) {
        $this->data = $data;
        $this->files = $files;
        $this->headers = $headers;
        $this->route = $route;
        
    }

    public function jsonSerialize() {
        return ['data'=>$this->data,'headers'=>$this->headers,'files'=>$this->files,'route'=>$this->route];
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
