const express = require('express');
const router = express.Router();
const pedidoController = require('../controllers/pedidoController');

// Rutas para pedidos
router.post('/', pedidoController.crearPedido);
router.get('/', pedidoController.obtenerPedidos);
router.get('/:id', pedidoController.obtenerPedido);
router.put('/:id', pedidoController.actualizarPedido);
router.delete('/:id', pedidoController.eliminarPedido);

module.exports = router; 