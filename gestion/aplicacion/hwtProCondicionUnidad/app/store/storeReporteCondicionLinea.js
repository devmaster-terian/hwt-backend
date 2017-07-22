/*
 * File: app/store/storeReporteCondicionLinea.js
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

Ext.define('hwtProCondicionUnidad.store.storeReporteCondicionLinea', {
    extend: 'Ext.data.Store',

    requires: [
        'hwtProCondicionUnidad.model.modelReporteCondicionLinea',
        'Ext.data.proxy.Rest',
        'Ext.data.reader.Json'
    ],

    constructor: function(cfg) {
        var me = this;
        cfg = cfg || {};
        me.callParent([Ext.apply({
            pageSize: 150,
            storeId: 'storeReporteCondicionLinea',
            model: 'hwtProCondicionUnidad.model.modelReporteCondicionLinea',
            proxy: {
                type: 'rest',
                reader: {
                    type: 'json',
                    rootProperty: function(data) {
                        var storeReporteCondicionLinea = Ext.getStore('storeReporteCondicionLinea');
                        var rawData = storeReporteCondicionLinea.getProxy().getReader().rawData;
                        return rawData.hwtReporteCondicionLinea;
                    }
                }
            },
            listeners: {
                beforeload: {
                    fn: me.onStoreBeforeLoad,
                    scope: me
                }
            }
        }, cfg)]);
    },

    onStoreBeforeLoad: function(store, operation, eOpts) {
        var storeReporteCondicionLinea = Ext.getStore('storeReporteCondicionLinea');
        var proxyCliente = storeReporteCondicionLinea.getProxy();

        var objJsonData = new Object();
        objJsonData.page  = storeReporteCondicionLinea.currentPage;
        objJsonData.start = (storeReporteCondicionLinea.currentPage - 1) * storeReporteCondicionLinea.pageSize;
        objJsonData.limit = storeReporteCondicionLinea.pageSize;
        objJsonData.cbxOpcionSeccion = elf.readElement('cbxOpcionSeccion');
        objJsonData.tfNumReporte     = elf.readElement('tfNumReporte');


        var objJsonRequest = new Object();
        objJsonRequest.apiController = 'apiCondicionUnidad';
        objJsonRequest.apiMethod     = 'listaReporteCondicionLinea';
        objJsonRequest.apiData       = JSON.stringify(objJsonData);

        proxyCliente.api.read        = elf.setApiDataBridge(objJsonRequest.apiController);
        proxyCliente.extraParams     = objJsonRequest;
    }

});