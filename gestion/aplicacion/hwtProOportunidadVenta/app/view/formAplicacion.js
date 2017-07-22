/*
 * File: app/view/formAplicacion.js
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

Ext.define('hwtProOportunidadVenta.view.formAplicacion', {
    extend: 'Ext.form.Panel',
    alias: 'widget.formAplicacion',

    requires: [
        'hwtProOportunidadVenta.view.formAplicacionViewModel',
        'Ext.button.Button',
        'Ext.toolbar.Separator',
        'Ext.toolbar.Fill',
        'Ext.grid.Panel',
        'Ext.view.Table',
        'Ext.toolbar.Paging',
        'Ext.grid.column.Column'
    ],

    viewModel: {
        type: 'formaplicacion'
    },
    id: 'formAplicacion',
    itemId: 'formAplicacion',
    bodyCls: 'formBackground',
    bodyPadding: 10,
    defaultListenerScope: true,

    dockedItems: [
        {
            xtype: 'toolbar',
            cls: 'toolbarBackground',
            dock: 'top',
            id: 'toolbarPrincipal',
            itemId: 'toolbarPrincipal',
            items: [
                {
                    xtype: 'button',
                    id: 'btnVisualizar',
                    itemId: 'btnVisualizar',
                    width: 130,
                    iconCls: 'fa fa-eye icon16 iconColorDarkBlue',
                    text: 'Visualizar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickVisualizar'
                    }
                },
                {
                    xtype: 'button',
                    id: 'btnBuscar',
                    itemId: 'btnBuscar',
                    width: 130,
                    iconCls: 'fa fa-search icon16 iconColorDarkBlue',
                    text: 'Buscar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickBuscar'
                    }
                },
                {
                    xtype: 'tbseparator',
                    width: 50
                },
                {
                    xtype: 'button',
                    id: 'btnCrear',
                    itemId: 'btnCrear',
                    width: 130,
                    iconCls: 'fa fa-plus-square icon16 iconColorGreen',
                    text: 'Crear',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickCrear'
                    }
                },
                {
                    xtype: 'button',
                    id: 'btnActualizar',
                    itemId: 'btnActualizar',
                    width: 130,
                    iconCls: 'fa fa-pencil-square icon16 iconColorGreen',
                    text: 'Actualizar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickActualizar'
                    }
                },
                {
                    xtype: 'tbseparator',
                    width: 50
                },
                {
                    xtype: 'button',
                    id: 'btnEliminar',
                    itemId: 'btnEliminar',
                    width: 130,
                    iconCls: 'fa fa-trash icon16 iconColorDarkRed',
                    text: 'Eliminar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickEliminar'
                    }
                },
                {
                    xtype: 'button',
                    id: 'btnReporte',
                    itemId: 'btnReporte',
                    width: 130,
                    iconCls: 'fa fa-th  icon16 iconColorDarkBlue',
                    text: 'Reporte',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickReporte'
                    }
                },
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button',
                    id: 'btnSalir',
                    itemId: 'btnSalir',
                    width: 130,
                    iconCls: 'fa fa-external-link-square  icon16 iconColorRed',
                    text: 'Salir',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClickSalir'
                    }
                }
            ]
        }
    ],
    items: [
        {
            xtype: 'gridpanel',
            reference: 'gridOportunidadVenta',
            id: 'gridOportunidadVenta',
            itemId: 'gridOportunidadVenta',
            title: 'gridOportunidadVenta',
            store: 'storeOportunidadVenta',
            dockedItems: [
                {
                    xtype: 'pagingtoolbar',
                    dock: 'bottom',
                    width: 360,
                    displayInfo: true
                }
            ],
            columns: [
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'gerente_regional_nombre',
                    text: 'Gerente Regional Nombre'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'vendedor_nombre',
                    text: 'Vendedor Nombre'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'visita_fecha',
                    text: 'Visita Fecha'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'visita_semana',
                    text: 'Visita Semana'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'tipo_solicitante',
                    text: 'Tipo Solicitante'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'tipo_empresa',
                    text: 'Tipo Empresa'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'contacto_nombre',
                    text: 'Contacto Nombre'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'contacto_cargo',
                    text: 'Contacto Cargo'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'contacto_telefono',
                    text: 'Contacto Telefono'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'contacto_movil',
                    text: 'Contacto Movil'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'contacto_email',
                    text: 'Contacto Email'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'consecionario_descripcion',
                    text: 'Consecionario Descripcion'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'solicitud_estado',
                    text: 'Solicitud Estado'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'solicitud_ciudad',
                    text: 'Solicitud Ciudad'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'cantidad',
                    text: 'Cantidad'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'marca',
                    text: 'Marca'
                },
                {
                    xtype: 'gridcolumn',
                    dataIndex: 'modelo',
                    text: 'Modelo'
                }
            ]
        }
    ],

    onButtonClickVisualizar: function(button, e, eOpts) {

    },

    onButtonClickBuscar: function(button, e, eOpts) {

    },

    onButtonClickCrear: function(button, e, eOpts) {
        elf.openWindow('winOportunidadVenta');
    },

    onButtonClickActualizar: function(button, e, eOpts) {

    },

    onButtonClickEliminar: function(button, e, eOpts) {

    },

    onButtonClickReporte: function(button, e, eOpts) {

    },

    onButtonClickSalir: function(button, e, eOpts) {
        elf.stopApp('hwtProOportunidadVenta');
    }

});