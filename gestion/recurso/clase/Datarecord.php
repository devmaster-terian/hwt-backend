<?php

class Datarecord{
    public function createProperty($name, $value){
        $this->{$name} = $value;
    }
}
