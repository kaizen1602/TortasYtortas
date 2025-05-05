<?php
require_once 'BaseModel.php';
class ProductoModel extends BaseModel {
    public function __construct() {
        parent::__construct('productos');
    }
} 