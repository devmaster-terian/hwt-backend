/*
 * File: app/model/modPedidoVenta.js
 *
 * This file was generated by Sencha Architect version 4.1.2.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 5.1.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 5.1.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('hwtProPedidoVentas.model.modPedidoVenta', {
    extend: 'Ext.data.Model',

    requires: [
        'Ext.data.field.Field'
    ],

    fields: [
        {
            name: 'rowid'
        },
        {
            name: 'num_pedido'
        },
        {
            name: 'fecha_pedido'
        },
        {
            name: 'codigo_gerente_regional'
        },
        {
            name: 'gerente_regional_nombre'
        },
        {
            name: 'codigo_vendedor'
        },
        {
            name: 'vendedor_nombre'
        },
        {
            name: 'codigo_consecionario'
        },
        {
            name: 'concesionario_descripcion'
        },
        {
            name: 'consecionario_sucursal'
        },
        {
            name: 'codigo_cliente'
        },
        {
            name: 'cliente_nombre'
        },
        {
            name: 'cantidad_unidades'
        },
        {
            name: 'valor_subtotal'
        },
        {
            name: 'valor_total'
        },
        {
            name: 'valor_con_cargo_cliente'
        },
        {
            name: 'valor_sin_cargo_cliente'
        },
        {
            name: 'tipo_entrega'
        },
        {
            name: 'codigo_consecionario_entrega'
        },
        {
            name: 'codigo_sucursal_consecionario_entrega'
        },
        {
            name: 'entrega_observaciones'
        },
        {
            name: 'integracion_clave_erp'
        },
        {
            name: 'integracion_fecha'
        },
        {
            name: 'situacion_pedido'
        },
        {
            name: 'situacion_pedido_descripcion'
        }
    ]
});