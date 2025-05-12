const Pedido = require('../models/Pedido');

// Crear un nuevo pedido
exports.crearPedido = async (req, res) => {
    try {
        const pedido = new Pedido(req.body);
        await pedido.save();
        res.status(201).json(pedido);
    } catch (error) {
        res.status(400).json({ mensaje: 'Error al crear el pedido', error: error.message });
    }
};

// Obtener todos los pedidos
exports.obtenerPedidos = async (req, res) => {
    try {
        const pedidos = await Pedido.find().populate('productos.producto');
        res.json(pedidos);
    } catch (error) {
        res.status(500).json({ mensaje: 'Error al obtener los pedidos', error: error.message });
    }
};

// Obtener un pedido por ID
exports.obtenerPedido = async (req, res) => {
    try {
        const pedido = await Pedido.findById(req.params.id).populate('productos.producto');
        if (!pedido) {
            return res.status(404).json({ mensaje: 'Pedido no encontrado' });
        }
        res.json(pedido);
    } catch (error) {
        res.status(500).json({ mensaje: 'Error al obtener el pedido', error: error.message });
    }
};

// Actualizar un pedido
exports.actualizarPedido = async (req, res) => {
    try {
        const pedido = await Pedido.findByIdAndUpdate(
            req.params.id,
            req.body,
            { new: true, runValidators: true }
        );
        if (!pedido) {
            return res.status(404).json({ mensaje: 'Pedido no encontrado' });
        }
        res.json(pedido);
    } catch (error) {
        res.status(400).json({ mensaje: 'Error al actualizar el pedido', error: error.message });
    }
};

// Eliminar un pedido
exports.eliminarPedido = async (req, res) => {
    try {
        const pedido = await Pedido.findByIdAndDelete(req.params.id);
        if (!pedido) {
            return res.status(404).json({ mensaje: 'Pedido no encontrado' });
        }
        res.json({ mensaje: 'Pedido eliminado correctamente' });
    } catch (error) {
        res.status(500).json({ mensaje: 'Error al eliminar el pedido', error: error.message });
    }
}; 