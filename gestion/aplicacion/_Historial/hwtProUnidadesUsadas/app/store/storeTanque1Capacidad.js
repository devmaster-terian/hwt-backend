/*
 * File: app/store/storeTanque1Capacidad.js
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

Ext.define('hwtProUnidadesUsadas.store.storeTanque1Capacidad', {
    extend: 'Ext.data.Store',

    requires: [
        'hwtProUnidadesUsadas.model.modTanque1Capacidad'
    ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            storeId: 'storeTanque1Capacidad',
            model: 'hwtProUnidadesUsadas.model.modTanque1Capacidad'
        }, cfg)]);
    }
});