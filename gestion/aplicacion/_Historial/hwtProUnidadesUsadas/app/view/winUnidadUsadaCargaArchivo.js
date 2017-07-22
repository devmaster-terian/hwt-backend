/*
 * File: app/view/winUnidadUsadaCargaArchivo.js
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

Ext.define('hwtProUnidadesUsadas.view.winUnidadUsadaCargaArchivo', {
    extend: 'Ext.window.Window',
    alias: 'widget.winUnidadUsadaCargaArchivo',

    requires: [
        'hwtProUnidadesUsadas.view.winUnidadUsadaCargaArchivoViewModel',
        'Ext.toolbar.Toolbar',
        'Ext.toolbar.Separator',
        'Ext.toolbar.Fill',
        'Ext.form.Panel',
        'Ext.form.FieldSet',
        'Ext.form.field.File',
        'Ext.form.field.FileButton'
    ],

    viewModel: {
        type: 'winunidadusadacargaarchivo'
    },
    modal: true,
    id: 'winUnidadUsadaCargaArchivo',
    itemId: 'winUnidadUsadaCargaArchivo',
    width: 800,
    closable: false,
    title: 'Carga de Archivo de Unidad',
    defaultListenerScope: true,

    dockedItems: [
        {
            xtype: 'toolbar',
            cls: 'toolbarBackground',
            dock: 'top',
            items: [
                {
                    xtype: 'tbseparator'
                },
                {
                    xtype: 'tbfill'
                },
                {
                    xtype: 'button',
                    width: 130,
                    iconCls: 'fa fa-window-close icon16 iconColorRed',
                    text: 'Cerrar',
                    textAlign: 'left',
                    listeners: {
                        click: 'onButtonClick'
                    }
                }
            ]
        }
    ],
    items: [
        {
            xtype: 'form',
            id: 'formCargaArchivo',
            itemId: 'formCargaArchivo',
            layout: 'column',
            bodyCls: 'formBackground',
            items: [
                {
                    xtype: 'fieldset',
                    columnWidth: 1,
                    id: 'fieldsetUnidadCarga',
                    itemId: 'fieldsetUnidadCarga',
                    margin: '0 5 5 5',
                    layout: 'column',
                    title: '<b>Unidad</b>',
                    items: [
                        {
                            xtype: 'textfield',
                            columnWidth: 1,
                            id: 'tfCodigoUnidadCarga',
                            itemId: 'tfCodigoUnidadCarga',
                            maxWidth: 200,
                            fieldLabel: 'Código'
                        },
                        {
                            xtype: 'textfield',
                            columnWidth: 1,
                            id: 'tfModeloUnidadCarga',
                            itemId: 'tfModeloUnidadCarga',
                            margin: '0 0 5 5',
                            maxWidth: 200,
                            fieldLabel: 'Modelo'
                        }
                    ]
                },
                {
                    xtype: 'fieldset',
                    columnWidth: 1,
                    id: 'fieldsetCargaArchivo',
                    itemId: 'fieldsetCargaArchivo',
                    margin: '0 5 5 5',
                    layout: 'column',
                    title: '<b>Carga de Archivo</b>',
                    items: [
                        {
                            xtype: 'filefield',
                            columnWidth: 0.9,
                            id: 'tfArchivoCarga',
                            itemId: 'tfArchivoCarga',
                            margin: '0 5 5 0',
                            fieldLabel: 'Archivo',
                            name: 'tfArchivoCarga',
                            buttonText: 'Buscar',
                            buttonConfig: {
                                xtype: 'filebutton',
                                id: 'btnBuscarArchivoCarga',
                                itemId: 'btnBuscarArchivoCarga',
                                width: 130,
                                iconCls: 'fa fa-search icon16 iconColorWhite',
                                text: 'Buscar',
                                textAlign: 'left'
                            }
                        },
                        {
                            xtype: 'button',
                            id: 'btnCargarArchivo',
                            itemId: 'btnCargarArchivo',
                            minWidth: 0.1,
                            width: 130,
                            iconCls: 'fa fa-cloud-upload icon16 iconColorWhite',
                            text: 'Cargar',
                            textAlign: 'left',
                            listeners: {
                                click: 'onBtnCargarArchivoClick'
                            }
                        }
                    ]
                }
            ],
            listeners: {
                drop: 'onContainerDrop',
                dragover: 'onContainerDragOver'
            }
        }
    ],

    onButtonClick: function(button, e, eOpts) {
        elf.closeWindow('winUnidadUsadaCargaArchivo');
    },

    onBtnCargarArchivoClick: function(button, e, eOpts) {
        var formCargaArchivo = Ext.getCmp('formCargaArchivo');
        if(formCargaArchivo.isValid()){
            var apiController = 'apiUnidadUsada';
            var apiMethod     = 'cargaImagenUnidad';

            var objParamsEnvio = new Object();
            objParamsEnvio.windowUpload  = 'winUnidadUsadaCargaArchivo';
            objParamsEnvio.fieldFileLoad = 'tfArchivoCarga';
            objParamsEnvio.apiController = apiController;
            objParamsEnvio.apiMethod     = apiMethod;
            objParamsEnvio.functionUpload = function(){
                Ext.getCmp('tabPanelGaleria').actualizaImagenes();
            };

            objParamsEnvio.codigo        = elf.readElement('tfCodigoUnidadCarga');
            objParamsEnvio.modelo        = elf.readElement('tfModeloUnidadCarga');

            elf.sendFile('formCargaArchivo',objParamsEnvio);



        }
    },

    onContainerDrop: function(container) {
        console.log('Elemento dropeado');
    },

    onContainerDragOver: function(container) {
        console.log('Draguienado un Archivo');
        if (!container.browserEvent.dataTransfer || Ext.Array.from(container.browserEvent.dataTransfer.types).indexOf('Files') === -1) {
            return;
        }
    }

});