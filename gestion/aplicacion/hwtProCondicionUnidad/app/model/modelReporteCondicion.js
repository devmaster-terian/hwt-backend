/*
 * File: app/model/modelReporteCondicion.js
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

Ext.define('hwtProCondicionUnidad.model.modelReporteCondicion', {
    extend: 'Ext.data.Model',

    requires: [
        'Ext.data.field.Field'
    ],

    fields: [
        {
            name: 'rowid'
        },
        {
            name: 'num_reporte'
        },
        {
            name: 'vin'
        },
        {
            name: 'fecha_reporte'
        },
        {
            name: 'usuario'
        },
        {
            name: 'num_reparaciones'
        },
        {
            name: 'precio_total_estimado'
        }
    ]
});