<?php
namespace SFW;

class Request implements \JsonSerializable {
    public function __construct(Array $data,Array $files,Array $headers) {
        $this->data = $data;
        $this->files = $files;
        $this->headers = $headers;
        
    }

    public function jsonSerialize() {
        return ['data'=>$this->data,'headers'=>$this->headers,'files'=>$this->files];
    }
}
