<?php
require_once 'BaseModel.php';
class ClienteModel extends BaseModel {
    public function __construct() {
        parent::__construct('clientes');
    }
} 