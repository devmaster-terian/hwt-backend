/*
 * File: app/view/winBuscaCliente.js
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

Ext.define('hwtProPedidoVentas.view.winBuscaCliente', {
    extend: 'Ext.window.Window',
    alias: 'widget.winBuscaCliente',

    requires: [
        'hwtProPedidoVentas.view.winBuscaClienteViewModel',
        'Ext.form.Panel',
        'Ext.form.FieldSet',
        'Ext.form.field.Text',
        'Ext.button.Button',
        'Ext.grid.Panel',
        'Ext.view.Table',
        'Ext.toolbar.Paging',
        'Ext.grid.column.Column',
        'Ext.toolbar.Fill'
    ],

    viewModel: {
        type: 'winbuscacliente'
    },
    modal: true,
    id: 'winBuscaCliente',
    itemId: 'winBuscaCliente',
    width: 600,
    closable: false,
    title: 'Buscar Cliente',
    defaultListenerScope: true,

    items: [
        {
            xtype: 'form',
            seleccionaRegistro: function() {
                registroCliente = Ext.getCmp('gridBuscaCliente').recordActivo;

                if(Ext.getCmp('formBuscaCliente').arrayCamposDespliegue !== undefined){
                    var arrayCamposDespliegue = Ext.getCmp('formBuscaCliente').arrayCamposDespliegue;
                    arrayCamposDespliegue.forEach(Ext.getCmp('formBuscaCliente').escribeCampo);
                }
                else{
                    elf.writeElement('tfCodigoCliente'  ,registroCliente.codigo_cliente);
                    elf.writeElement('tfNombreCliente'  ,registroCliente.razon_social);
                    elf.writeElement('tfRfcCliente'     ,registroCliente.rfc);
                    elf.writeElement('tfContactoCliente',registroCliente.contacto_nombre);
                }

                elf.closeWindow('winBuscaCliente');
            },
            escribeCampo: function(element, index, array) {
                elf.writeElement(element.campoForm,
                registroCliente[element.campoDato]);

            },
            id: 'formBuscaCliente',
            itemId: 'formBuscaCliente',
            layout: 'column',
            bodyCls: 'formBackground',
            bodyPadding: 10,
            items: [
                {
                    xtype: 'fieldset',
                    columnWidth: 0.7,
                    id: 'fieldsetParametrosBuscaCliente',
                    itemId: 'fieldsetParametrosBuscaCliente',
                    title: '<b>Parámetros de Búsqueda</b>',
                    items: [
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            id: 'tfParamCodigo',
                            itemId: 'tfParamCodigo',
                            fieldLabel: 'Código'
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            id: 'tfParamNombreCorto',
                            itemId: 'tfParamNombreCorto',
                            fieldLabel: 'Nombre'
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            id: 'tfParamRazonSocial',
                            itemId: 'tfParamRazonSocial',
                            fieldLabel: 'Razón Social'
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            id: 'tfParamRFC',
                            itemId: 'tfParamRFC',
                            fieldLabel: 'RFC'
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    columnWidth: 0.3,
                    height: 128,
                    id: 'fieldsetAccionesBuscaCliente',
                    itemId: 'fieldsetAccionesBuscaCliente',
                    margin: '9 0 0 5',
                    layout: 'column',
                    items: [
                        {
                            xtype: 'button',
                            columnWidth: 1,
                            cls: 'botonZoom',
                            id: 'btnBuscaCliente',
                            itemId: 'btnBuscaCliente',
                            margin: '9 0 63',
                            iconCls: 'fa fa-search icon16 iconColorWhite',
                            text: 'Buscar Clientes',
                            textAlign: 'left',
                            listeners: {
                                click: 'onBtnBuscaClienteClick'
                            }
                        }
                    ]
                },
                {
                    xtype: 'gridpanel',
                    columnWidth: 1,
                    reference: 'gridBuscaCliente',
                    height: 300,
                    id: 'gridBuscaCliente',
                    itemId: 'gridBuscaCliente',
                    title: 'Clientes',
                    forceFit: true,
                    store: 'storeBuscaCliente',
                    viewConfig: {
                        id: 'viewTableGridBuscaCliente'
                    },
                    dockedItems: [
                        {
                            xtype: 'pagingtoolbar',
                            dock: 'bottom',
                            id: 'toolbarGridBuscaCliente',
                            itemId: 'toolbarGridBuscaCliente',
                            width: 360,
                            displayInfo: true,
                            store: 'storeBuscaCliente'
                        }
                    ],
                    columns: [
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'codigo_cliente',
                            text: 'Codigo</br>Cliente'
                        },
                        {
                            xtype: 'gridcolumn',
                            dataIndex: 'nombre_corto',
                            text: 'Nombre Corto'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 150,
                            dataIndex: 'rfc',
                            text: 'Rfc'
                        },
                        {
                            xtype: 'gridcolumn',
                            width: 150,
                            dataIndex: 'razon_social',
                            text: 'Razon Social'
                        }
                    ],
                    listeners: {
                        itemclick: 'onGridBuscaClienteItemClick',
                        itemdblclick: 'onGridBuscaClienteItemDblClick'
                    }
                }
            ]
        }
    ],
    dockedItems: [
        {
            xtype: 'toolbar',
            cls: 'toolbarBackground',
            dock: 'bottom',
            id: 'toolbarBuscaCliente',
            itemId: 'toolbarBuscaCliente',
            items: [
                {
                    xtype: 'button',
                    id: 'btnConfirmarBuscaCliente',
                    itemId: 'btnConfirmarBuscaCliente',
                    width: 130,
                    iconCls: 'fa fa-check-square icon16 iconColorGreen',
                    text: 'Confirmar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onBtnConfirmarBuscaClienteClick'
                    }
                },
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button',
                    id: 'btnCerrarBuscaCliente',
                    itemId: 'btnCerrarBuscaCliente',
                    width: 130,
                    iconCls: 'fa fa-window-close icon16 iconColorRed',
                    text: 'Cerrar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onBtnCerrarBuscaClienteClick'
                    }
                }
            ]
        }
    ],

    onBtnBuscaClienteClick: function(button, e, eOpts) {
        elf.refreshGrid('gridBuscaCliente');
    },

    onGridBuscaClienteItemClick: function(dataview, record, item, index, e, eOpts) {
        Ext.getCmp('gridBuscaCliente').recordActivo = record.data;
    },

    onGridBuscaClienteItemDblClick: function(dataview, record, item, index, e, eOpts) {
        Ext.getCmp('formBuscaCliente').seleccionaRegistro();
    },

    onBtnConfirmarBuscaClienteClick: function(button, e, eOpts) {
        Ext.getCmp('formBuscaCliente').seleccionaRegistro();
    },

    onBtnCerrarBuscaClienteClick: function(button, e, eOpts) {
        elf.closeWindow('winBuscaCliente');
    }

});