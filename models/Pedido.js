const mongoose = require('mongoose');

const pedidoSchema = new mongoose.Schema({
    cliente: {
        nombre: { type: String, required: true },
        email: { type: String, required: true },
        telefono: { type: String, required: true }
    },
    productos: [{
        producto: { type: mongoose.Schema.Types.ObjectId, ref: 'Producto', required: true },
        cantidad: { type: Number, required: true },
        precioUnitario: { type: Number, required: true }
    }],
    fechaEntrega: { type: Date, required: true },
    estado: {
        type: String,
        enum: ['pendiente', 'en_proceso', 'completado', 'cancelado'],
        default: 'pendiente'
    },
    total: { type: Number, required: true },
    notas: String,
    fechaCreacion: { type: Date, default: Date.now }
});

module.exports = mongoose.model('Pedido', pedidoSchema); 