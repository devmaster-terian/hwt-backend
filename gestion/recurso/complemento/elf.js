Ext.define('modelSession',
           {
               extend: 'Ext.data.Model',
               fields: [
                   {name: 'company_code',   type: 'string'},
                   {nafome: 'company_name',   type: 'string'},
                   {name: 'user_id',        type: 'string'},
                   {name: 'user_name',      type: 'string'},
                   {name: 'user_profile',   type: 'string'},
                   {name: 'user_email',     type: 'string'},
                   {name: 'profile_code',   type: 'string'},
                   {name: 'system_code',    type: 'string'},
                   {name: 'system_name',    type: 'string'},
                   {name: 'system_version', type: 'string'}
               ]
           });

Ext.define('storeSession',
           {
               extend: 'Ext.data.Store',
               model: 'modelSession',
               proxy: {
                   type: 'sessionstorage',
                   id: 'storeSession'
               }
           });

var environment = {
    defaultLanguage: function () {
        return 'Spanish';
    }
};

var elf = {
    getWeekOfYear: function (date) {
        var target = new Date(date.valueOf()),
            dayNumber = (date.getUTCDay() + 6) % 7,
            firstThursday;

        target.setUTCDate(target.getUTCDate() - dayNumber + 3);
        firstThursday = target.valueOf();
        target.setUTCMonth(0, 1);

        if (target.getUTCDay() !== 4) {
            target.setUTCMonth(0, 1 + ((4 - target.getUTCDay()) + 7) % 7);
        }

        return Math.ceil((firstThursday - target) / (7 * 24 * 3600 * 1000)) + 1;
    },
    loadComboBoxConfig: function (element, index, array) {
        var objConfig = element;
        var idCombo;
        var jsonData;
        var listaCombo;

        if (objConfig.idComboBox === undefined) {
            idCombo = 'cbx' + objConfig.id;
        }
        else {
            idCombo = objConfig.idComboBox;
        }

        if (objConfig.idDataBridge === undefined) {
            jsonData = elf.getInfoDataBridge('datosOpciones');
            listaCombo = 'opciones' + objConfig.id;
        }
        else {
            jsonData = elf.getInfoDataBridge(objConfig.idDataBridge);
            listaCombo = objConfig.id;
        }

        if (Ext.getCmp(idCombo) !== undefined) {
            elf.setComboBox(idCombo,
                jsonData,
                listaCombo,
                objConfig.fieldValue,
                objConfig.fieldDisplay,
                objConfig.filterDefault,
                objConfig.filterField,
                objConfig.filterValue);
        }
        else {
            console.warn('elf::loadComboConfig: - ComboBox was not found: ' + idCombo);
        }
    },
    loadCombos: function(element,index,array){
        var jsonData = elf.getInfoDataBridge('datosOpciones');
        var idCombo    = 'cbx' + element;
        var listaCombo = 'opciones' + element;

        if(Ext.getCmp(idCombo) !== undefined){
            elf.setComboBox(idCombo,
                            jsonData,
                            listaCombo,
                            'descripcion',
                            'descripcion');
        }
        else{
            console.warn('elf::loadCombos: - ComboBox wa not found: ' + idCombo);
        }        
    },
    hideElement: function(pElement){
        var element = Ext.getCmp(pElement);

        if(element === undefined){
            console.error('Elf::hideElement:Element ' + pElement + ' was not found');
        }
        else{
            element.setVisible(false);
        }

    },
    showElement: function(pElement){
        var element = Ext.getCmp(pElement);

        if(element === undefined){
            console.error('Elf::showElement:Element ' + pElement + ' was not found');
        }
        else{
            element.setVisible(true);
        }

    },    
    getGeneratedFile: function(pFileName){
        var finalName = '../../reporte/' + pFileName + '.xlsx';
        return finalName;
    },
    sendFile: function(pIdForm,pObjParamsEnvio){
        var formSend = Ext.getCmp(pIdForm);

        if(formSend !== undefined){
            var urlAjax = '../../controlador/' + pObjParamsEnvio.apiController + '.php';
            formSend.submit({
                url: urlAjax,
                waitMsg: 'Cargando Archivo...',
                params: pObjParamsEnvio,
                success: function(formPanel, action) {
                    var idDataBridge = pObjParamsEnvio.apiMethod;
                    elf.setInfoDataBridge(idDataBridge,
                                          action.response.responseText);                    

                    var data = Ext.decode(action.response.responseText);

                    var msgTipo      = 'INFORMATION';
                    var msgTitulo    = 'Proceso finalizado';
                    var msgContenido = data.message[0].message;
                    elf.message(msgTipo,
                                msgTitulo,
                                msgContenido);

                    pObjParamsEnvio.functionUpload();

                    var windowUpload = Ext.getCmp(pObjParamsEnvio.windowUpload);
                    windowUpload.close();
                },
                failure: function(formPanel, action){
                    var data = Ext.decode(action.response.responseText);

                    var msgTipo      = 'ERROR';
                    var msgTitulo    = 'Proceso finalizado';
                    var msgContenido = 'Falló la Carga del Archivo';

                    if(data.message[0].message !== undefined){
                        msgContenido = msgContenido + '</br>'    +
                            '<i><font color=darkred>' + 
                            data.message[0].message   + 
                            '</font></i>';
                    }

                    elf.message(msgTipo,
                                msgTitulo,
                                msgContenido);
                }
            });
        }
        else{
            console.warn('elf::sendFile: Form ' + pIdForm + ' was not found.');
        }
    },
    doFieldAsNumber: function(pIdField){
        var numberData = elf.readElement(pIdField,'number');

        if(parseInt(numberData,0) === 0){
            return;
        }
        var numberDataFormat = Ext.util.Format.number(numberData, '0,000.00');

        elf.writeElement(pIdField,numberDataFormat);

    },
    defaultDataApp: function(){

        var appTimeout = 300000;
        Ext.Ajax.timeout = appTimeout;
        Ext.override(Ext.data.proxy.Ajax,    { timeout: appTimeout });
        Ext.override(Ext.form.action.Action, { timeout: appTimeout });

        Ext.util.Format.decimalSeparator  = '.';
        Ext.util.Format.thousandSeparator = ',';

        var urlIcon = '../../recurso/imagen/comun/favicon.ico';
        elf.changeAppIcon(urlIcon);
        var nombreSistema = elf.getSessionData('system_name');
        if(nombreSistema === ''){
            nombreSistema = 'HWT - Gestión de Unidades Usadas';
        }

        Ext.getDoc().dom.title = nombreSistema;
    },
    doFunction: function(pForm,pFunction){
        var formFunction = Ext.getCmp(pForm);
        if(formFunction !== undefined){
            formFunction.pFunction();
        }
        else{
            console.warn('elf::doFunction: Form was not found');
        }
    },
    closeWindow: function (pIdWindow){
        var winActive = Ext.getCmp(pIdWindow);
        winActive.close();  
    },
    openWindow: function (pIdWindow){
        var idWindow        = 'widget.' + pIdWindow;
        var winOpened = Ext.create(idWindow);

        winOpened.show();
    },

    refreshGrid: function (pIdGrid){
        var idStoreGrid = pIdGrid.replace('grid','store');
        var storeGrid   = Ext.getStore(idStoreGrid);

        if(storeGrid !== undefined){
            storeGrid.loadData([],false);
            storeGrid.load();
        }
        else{
            console.warn('refreshGrid: Store Grid was not found');
        }
    },

    renderInterface: function(pIdContainer,
                               pIdWidget,
                               pPadding){
        var widgetContainer = Ext.getCmp(pIdContainer);
        widgetContainer.doLayout();

        var heightWidget = Ext.getBody().getViewSize().height - pPadding;
        Ext.getCmp(pIdWidget).setHeight(heightWidget);    
    },
    getDataBridgeUrl: function (){
        var dataBridgeUrl = '../../controlador/';
        return dataBridgeUrl;
    },
    setApiDataBridge: function(pApiDataBridge){
        var apiDataBridge = '';
        apiDataBridge = elf.getDataBridgeUrl() + pApiDataBridge + '.php';
        return apiDataBridge;
    },
    setComboBox: function (pIdComboBox,
                            pJsonData,
                            pTableName,
                            pKeyElement,
                            pValueElement,
                            pDefaultValue,
                            pKeyFilter,
                            pKeyFilterValue,
                            pDebugMode
                           ){

        var entityName;
        var storeName;
        var storeCombo;     
        var jsonData;
        var valKeyField;
        var valValueField;
        var comboBoxData;
        var numRecords;
        var firstValue;
        var standardCombo;

        pKeyElement   = pKeyElement.toLowerCase();
        pValueElement = pValueElement.toLowerCase();
        jsonData      = pJsonData;
        entityName    = pIdComboBox.substring(3,pIdComboBox.length);
        storeName     = 'store' + entityName;
        storeCombo    = Ext.getStore(storeName);

        if(jsonData === undefined){
            console.warn('elf::setComboBox: jsonData is undefined');
            return;
        }

        if(storeCombo === undefined){
            entityName = pIdComboBox.substring(4,pIdComboBox.length);
            storeName  = 'store' + entityName.toLowerCase();
            storeCombo = Ext.getStore(storeName);        
        }

        if(storeCombo === undefined){
            console.warn('elf::setComboBox: Store was not found -> ' + storeName);
        }    

        valKeyField   = '';
        valValueField = '';
        comboBoxData  = new Array();
        standardCombo = Ext.getCmp(pIdComboBox);

        if(standardCombo === undefined){
            standardCombo = window.parent.Ext.getCmp(pIdComboBox);
        }   

        if(standardCombo === undefined){
            console.warn('elf::setComboBox: ComboBox or Store was not found -> ' + pIdComboBox);
        }   

        if(jsonData[pTableName] === undefined){
            console.warn('elf::setComboBox: Unable to find Table of Options -> ' + pTableName);
            return;
        }

        numRecords = Object.keys(jsonData[pTableName]).length;

        firstValue  = '';
        var iPos = 0;
        for(var iLoop=0; iLoop < numRecords; iLoop++){

            if(jsonData[pTableName][iLoop] !== null){

                valKeyField    = jsonData[pTableName][iLoop][pKeyElement];
                valValueField  = jsonData[pTableName][iLoop][pValueElement];


                if(pKeyFilter !== undefined){
                    var valKeyFilter      = jsonData[pTableName][iLoop][pKeyFilter];

                    if(valKeyFilter !== pKeyFilterValue){
                        continue;
                    }
                }

                if(firstValue === '' || firstValue === undefined ){
                    firstValue = valKeyField;
                }

                var objData = [valKeyField,valValueField];
                if(objData !== undefined){
                    comboBoxData[iPos] = objData;
                    iPos = iPos + 1;

                }
            }
        }

        /* ECRC: Cargando la informacion en el ComboBox */
        standardCombo.setStore(comboBoxData, false); 

        /* ECRC: Implementando el valor por defecto     */
        if(standardCombo !== undefined){
            if(pDefaultValue !== '' && pDefaultValue !== undefined){
                standardCombo.setValue(pDefaultValue);
            }

            if(pDefaultValue === undefined){
                standardCombo.setValue(firstValue);
            }   

            if(pDefaultValue === ''){
                standardCombo.setValue(firstValue);
            }       
        }
        else{
            console.warn('setComboBox: ComboBox or Store was not found -> ' + pIdComboBox);
        }

    },    

    loadComboBox: function ( pObjConfigComboBox){

        var boxWait = Ext.MessageBox.show({ 
            msg: 'Please wait, loading ComboBox...', 
            progressText: 'Procesando', 
            width:300, 
            wait:true, 
            waitConfig: {interval:200}
        });    


        var functionSuccessCombo = function(){
            var jsonData = elf.getInfoDataBridge(pObjConfigComboBox.pApiController);
            console.log('functionSuccessCombo');
            console.log(jsonData);

            var entityName;
            var storeName;
            var storeCombo;  
            var strJson;
            var jsonDataFormulario;
            var standardCombo;
            var mensajeError;
            var existeValorDefault;

            entityName = pObjConfigComboBox.pIdComboBox.substring(2,pObjConfigComboBox.pIdComboBox.length);
            storeName   = 'store' + entityName;
            storeCombo    = Ext.getStore(storeName);  

            if(storeCombo === undefined){
                entityName = pObjConfigComboBox.pIdComboBox.substring(4,pObjConfigComboBox.pIdComboBox.length);
                storeName   = 'store' + entityName;
                storeCombo    = Ext.getStore(storeName);        
            }    

            if(storeCombo === undefined){
                entityName = pObjConfigComboBox.pIdComboBox.substring(4,pObjConfigComboBox.pIdComboBox.length);
                storeName   = 'store' + entityName.toLowerCase();
                storeCombo    = Ext.getStore(storeName);        
            }

            if(storeCombo === undefined){
                console.warn('Trex: loadComboBox - Unable to find Store ' + storeName);
            }  


            standardCombo = Ext.getCmp(pObjConfigComboBox.pIdComboBox);

            if(standardCombo === undefined){
                standardCombo = window.parent.Ext.getCmp(pObjConfigComboBox.pIdComboBox);
            }  

            if(standardCombo === undefined){
                console.error('Trex: loadComboBox - Unable to find ComboBox ' + pObjConfigComboBox.pIdComboBox);
            }            
            valKeyField = '';
            valValueField  = '';
            comboBoxData = new Array();

            numRecords = Object.keys(jsonData[pObjConfigComboBox.pTableName]).length;
            firstValue  = '';
            for(var iLoop=0; iLoop < numRecords; iLoop++){
                if(jsonData[pObjConfigComboBox.pTableName][iLoop] !== null){

                    if(firstValue === ''){
                        firstValue = jsonData[pObjConfigComboBox.pTableName][iLoop][pObjConfigComboBox.pElementKey];
                    }

                    valKeyField = jsonData[pObjConfigComboBox.pTableName][iLoop][pObjConfigComboBox.pElementKey];
                    valValueField  = jsonData[pObjConfigComboBox.pTableName][iLoop][pObjConfigComboBox.pElementDescription];
                    //valValueField  = convierteAscci(valValueField);    

                    console.log('Registro para ComboBox:');
                    console.log(valKeyField + '->' + valValueField);

                    var objRecord = new Object();
                    objRecord[pObjConfigComboBox.pElementKey]         = valKeyField;
                    objRecord[pObjConfigComboBox.pElementDescription] = valValueField;


                    comboBoxData.push(objRecord);
                    //comboBoxData[iLoop] = [valKeyField,valValueField];

                    // ECRC: Assign default value
                    if(valKeyField == pObjConfigComboBox.pDefaultValue){
                        existeValorDefault = true;
                    }
                }
            }

            console.log('Información de Carga de Combo Box');
            console.log(comboBoxData);

            storeCombo.loadData(comboBoxData, false); 

            if(pObjConfigComboBox.pDefaultValue !== ''){
                standardCombo.setValue(pObjConfigComboBox.pDefaultValue);
            }

            if(pObjConfigComboBox.pDefaultValue === undefined || pObjConfigComboBox.pDefaultValue === ''){
                standardCombo.setValue(firstValue);
            }

            if(existeValorDefault === false){
                standardCombo.setValue(firstValue);
            }

            boxWait.hide();

            if(pObjConfigComboBox.pFunctionSuccess !== null &&pObjConfigComboBox.pFunctionSuccess !== undefined ){
                try{
                    pObjConfigComboBox.pFunctionSuccess();
                } catch(error){
                    console.error('Error en la Funcion: ' + pObjConfigComboBox.pFunctionSuccess);
                    console.error(error.message);
                    console.error('Verifique la Sintaxis de la Funcion.');
                }                        
            }                
            return;
        };

        var functionFailureCombo = function(){
            var jsonData = elf.getInfoDataBridge(pObjConfigComboBox.pApiController);
            console.log('functionFailureCombo');
            console.log(jsonData);

            numRecords = Object.keys(jsonData.dsRetorno.ttInformacion).length;

            for(var iLoop=0; iLoop <= numRecords; iLoop++){
                if(jsonData.dsRetorno.ttInformacion[iLoop] != null){

                    if(jsonData.dsRetorno.ttInformacion[iLoop].tipo == 'ERROR'){
                        mensajeError = '<font color=darkred size=2px><b>' + jsonData.dsRetorno.ttInformacion[iLoop].codInformacion + ' - ';
                        mensajeError += jsonData.dsRetorno.ttInformacion[iLoop].descInformacion + '</b></br>';
                        mensajeError += '<i>' + jsonData.dsRetorno.ttInformacion[iLoop].adicional + '</i></font></br>';
                    }
                }
            }        

            boxWait.hide();

            if(pObjConfigComboBox.pFunctionFailure !== null){
                try{
                    pObjConfigComboBox.pFunctionFailure();

                } catch(error){
                    console.error('Error en la Funcion: ' + pObjConfigComboBox.pFunctionFailure);
                    console.error(error.message);
                    console.error('Verifique la Sintaxis de la Funcion.');
                }                    
            }                

            if(pObjConfigComboBox.pDebugMode === true){
                Ext.Msg.show({
                    title      : 'Error en API Backend',
                    msg        : mensajeError,
                    width      : 500,
                    buttons    : Ext.MessageBox.OK,
                    icon       : Ext.MessageBox.ERROR
                });
            }


            comboBoxData = '';
            storeCombo.loadData(comboBoxData, false); 
            standardCombo.setValue('');
            return;

        };       

        /////////////////////////////////////////////
        // ECRC: Preparing the dataBridge          //
        /////////////////////////////////////////////
        var objJsonData = {
            pFilterBy    : pObjConfigComboBox.pFilterBy
        };

        var objJsonRequest = {
            apiController : pObjConfigComboBox.pApiController,
            apiMethod     : pObjConfigComboBox.pApiMethod,
            apiData       : JSON.stringify(objJsonData)
        };

        elf.doDataBridge(objJsonRequest,
                         functionSuccessCombo,
                         null,
                         functionFailureCombo,
                         null);
        return;
    },    

    stopApp: function (idWindow) {
        var win = window.parent.Ext.getCmp(idWindow);
        if(win)win[win.closeAction]();  
    },

    startApp: function (idWindow) {

        /*
        var winWidget = Ext.getCmp(idWindow);

        if (!winWidget) {
            var widgetWindow = 'widget.' + idWindow;
            var winApplication = Ext.create(widgetWindow);
            winApplication.show();
            elf.hideScrollBars();
        }

        */
    },

    refreshGrid: function (idGrid) {
        var gridElement = Ext.getCmp(idGrid);

        if (gridElement !== undefined) {
            gridElement.getStore().loadData([], '');
            gridElement.getStore().load();
        }
        else {
            console.error('elf: Grid not found: ' + idGrid);
        }
    },

    changeAppIcon: function (src) {
        var link = document.createElement('link'),
            oldLink = document.getElementById('dynamic-favicon');
        link.id = 'dynamic-favicon';
        link.rel = 'icon';
        link.href = src;

        if (oldLink) {
            document.head.removeChild(oldLink);
        }
        document.head.appendChild(link);
    },

    generateSession: function (p_sessionData) {
        Ext.onReady(function () {

            var storeSession = Ext.create('storeSession');
            storeSession.getProxy().clear();

            storeSession.add({
                company_code   : p_sessionData.company_code,
                company_name   : p_sessionData.company_name,
                user_id        : p_sessionData.user_id,
                user_name      : p_sessionData.user_name,
                user_profile   : p_sessionData.user_profile,
                user_email     : p_sessionData.user_email,
                profile_code   : p_sessionData.profile_code,
                system_code    : p_sessionData.system_code,
                system_name    : p_sessionData.system_name,
                system_version : p_sessionData.system_version
            });

            storeSession.sync();
        });
    },

    getSessionData: function (p_data) {
        var returnValue = '';
        var storeSession = Ext.create('storeSession');

        storeSession.load(function (records, op, success) {
            var sessionApplication, iLoop;

            if(records[0] !== undefined){
                sessionApplication = records[0].data;
                returnValue = sessionApplication[p_data];
            }
            else{
                console.warn('elf::getSessionData: Session Data not available');
            }
        });

        return returnValue;
    },

    prepareFormFields: function (p_idForm, p_sufix) {
        var objJsonData = new Object();
        var formItems = Ext.getCmp(p_idForm).items;
        var formRadio = Ext.getCmp(p_idForm);

        for (iLoop = 0; iLoop < formItems.keys.length; iLoop++) {
            var nameElement = formItems.keys[iLoop];
            var element = Ext.getCmp(nameElement);

            if(element === undefined){

                if(p_sufix !== undefined){
                    nameElement = nameElement + p_sufix;
                    element = Ext.getCmp(nameElement);
                    if(element === undefined){
                        console.warn('elf::prepareFormFields: Element was not found with Sufix - ' + nameElement);
                        continue;                        
                    }
                }

                console.warn('elf::prepareFormFields: Element was not found - ' + nameElement);
                continue;
            }

            if (element.xtype === 'fieldset') {

                for (iLoopSet = 0; iLoopSet < element.items.keys.length; iLoopSet++) {
                    var field = Ext.getCmp(element.items.keys[iLoopSet]);

                    if(field !== undefined){
                        if (field.xtype === 'radiogroup') {

                            var radioGroup = Ext.getCmp(field.itemId).getValue();
                            var optionRadio = radioGroup[field.id];
                            objJsonData[field.itemId] = optionRadio;
                        }
                        else {
                            objJsonData[field.itemId] = field.value;
                        }
                    }
                    else{
                        var nameItem = element.items.keys[iLoopSet];
                        console.warn('elf::prepareFormFields - Field not found ' + nameItem);
                    }
                }
            } //fieldset

            if (element.xtype === 'tabpanel') {

                for (iLoopSet = 0; iLoopSet < element.items.keys.length; iLoopSet++) {
                    var field = Ext.getCmp(element.items.keys[iLoopSet]);

                    if(field !== undefined){

                        if (field.xtype === 'radiogroup') {

                            var radioGroup = Ext.getCmp(field.itemId).getValue();
                            var optionRadio = radioGroup[field.id];
                            objJsonData[field.itemId] = optionRadio;
                        }
                        else {
                            objJsonData[field.itemId] = elf.getValueWidget(field);
                        }

                        if (field.xtype === 'fieldset') {

                            for (iLoopSetFieldset = 0; iLoopSetFieldset < field.items.keys.length; iLoopSetFieldset++) {
                                var fieldObject = Ext.getCmp(field.items.keys[iLoopSetFieldset]);

                                if(fieldObject !== undefined){
                                    if (fieldObject.xtype === 'radiogroup') {

                                        var radioGroup = Ext.getCmp(fieldObject.itemId).getValue();
                                        var optionRadio = radioGroup[fieldObject.id];
                                        objJsonData[fieldObject.itemId] = optionRadio;
                                    }
                                    else {
                                        objJsonData[fieldObject.itemId] = elf.getValueWidget(fieldObject);
                                    }
                                }
                                else{
                                    var nameItem = field.items.keys[iLoopSetFieldset];
                                    console.warn('elf::prepareFormFields - Field not found ' + nameItem);
                                }
                            }
                        } //fieldset     

                        if (field.xtype === 'panel') {

                            for (iLoopSetPanel = 0; iLoopSetPanel < field.items.keys.length; iLoopSetPanel++) {
                                var fieldPanelObject = Ext.getCmp(field.items.keys[iLoopSetPanel]);

                                if(fieldPanelObject !== undefined){
                                    if (fieldPanelObject.xtype === 'radiogroup') {

                                        var radioGroup = Ext.getCmp(fieldPanelObject.itemId).getValue();
                                        var optionRadio = radioGroup[fieldPanelObject.id];
                                        objJsonData[fieldPanelObject.itemId] = optionRadio;
                                    }
                                    else {
                                        objJsonData[fieldPanelObject.itemId] = elf.getValueWidget(fieldPanelObject);
                                    }
                                }
                                else{
                                    var nameItem = field.items.keys[iLoopSetPanel];
                                    console.warn('elf::prepareFormFields - Field not found ' + nameItem);
                                }

                                if (fieldPanelObject.xtype === 'fieldset') {
                                    for (iLoopSetFieldsetPanel = 0; iLoopSetFieldsetPanel < fieldPanelObject.items.keys.length; iLoopSetFieldsetPanel++) {
                                        var fieldFieldsetPanelObject = Ext.getCmp(fieldPanelObject.items.keys[iLoopSetFieldsetPanel]);

                                        if(fieldFieldsetPanelObject !== undefined){
                                            if (fieldFieldsetPanelObject.xtype === 'radiogroup') {

                                                var radioGroup = Ext.getCmp(fieldFieldsetPanelObject.itemId).getValue();
                                                var optionRadio = radioGroup[fieldFieldsetPanelObject.id];
                                                objJsonData[fieldFieldsetPanelObject.itemId] = optionRadio;
                                            }
                                            else {
                                                objJsonData[fieldFieldsetPanelObject.itemId] = elf.getValueWidget(fieldFieldsetPanelObject);
                                            }

                                            if (fieldFieldsetPanelObject.xtype === 'fieldset') {
                                                for (iLoopFieldsetNested = 0; iLoopFieldsetNested < fieldFieldsetPanelObject.items.keys.length; iLoopFieldsetNested++) {
                                                    var fieldFieldsetNestedObject = Ext.getCmp(fieldFieldsetPanelObject.items.keys[iLoopFieldsetNested]);

                                                    if(fieldFieldsetNestedObject !== undefined){
                                                        if (fieldFieldsetNestedObject.xtype === 'radiogroup') {

                                                            var radioGroup = Ext.getCmp(fieldFieldsetNestedObject.itemId).getValue();
                                                            var optionRadio = radioGroup[fieldFieldsetNestedObject.id];
                                                            objJsonData[fieldFieldsetNestedObject.itemId] = optionRadio;
                                                        }
                                                        else {
                                                            objJsonData[fieldFieldsetNestedObject.itemId] = elf.getValueWidget(fieldFieldsetNestedObject);
                                                        }
                                                    }
                                                    else{
                                                        var nameItem = fieldFieldsetPanelObject.items.keys[iLoopFieldsetNested];
                                                        console.warn('elf::prepareFormFields - Field not found ' + nameItem);
                                                    }
                                                }                                                
                                            }//fieldset
                                        }
                                        else{
                                            var nameItem = fieldPanelObject.items.keys[iLoopSetFieldsetPanel];
                                            console.warn('elf::prepareFormFields - Field not found ' + nameItem);
                                        }
                                    }
                                } //fieldset                                 
                            }                            
                        }//panel
                    }
                    else{
                        var nameItem = element.items.keys[iLoopSet];
                        console.warn('elf::prepareFormFields - Field not found ' + nameItem);
                    }
                }


            } //tabpanel

        }

        //ECRC: Adding default fields
        objJsonData.company_code   = elf.getSessionData('company_code');
        objJsonData.company_name   = elf.getSessionData('company_name');
        objJsonData.user_id        = elf.getSessionData('user_id');
        objJsonData.user_name      = elf.getSessionData('user_name');
        objJsonData.user_profile   = elf.getSessionData('user_profil');
        objJsonData.user_email     = elf.getSessionData('user_email');
        objJsonData.profile_code   = elf.getSessionData('profile_code');
        objJsonData.system_code    = elf.getSessionData('system_code');
        objJsonData.system_name    = elf.getSessionData('system_name');
        objJsonData.system_version = elf.getSessionData('system_version');

        var returnValue = JSON.stringify(objJsonData);
        return returnValue;
    },
    getValueWidget: function(pObjWidget){
        var returnValue;
        if(pObjWidget.itemId!== undefined  && 
           pObjWidget.xtype !== 'panel'    && 
           pObjWidget.xtype !== 'fieldset' && 
           pObjWidget.xtype !== 'tabpanel' && 
           pObjWidget.xtype !== 'button'   && 
           pObjWidget.xtype !== 'image'    ){
            if(pObjWidget.fieldCls === 'formatDecimal'){
                returnValue = elf.readElement(pObjWidget.itemId,'number');
            }
            else{
                if(pObjWidget.itemId !== undefined){
                    returnValue = elf.readElement(pObjWidget.itemId);    

                    if(pObjWidget.xtype === 'datefield'){
                        returnValue = returnValue.substring(0,2) + '-' +
                            returnValue.substring(3,5) + '-' + 
                            returnValue.substring(6,10);

                    }
                }
            }
        }
        return returnValue;
    },

    valueFormFields: function(p_objData, p_idForm, p_sufix){
        var formComponent = Ext.getCmp(p_idForm);

        if(formComponent === undefined){
            console.warn('valueFormFields: Form was not found - ' + p_idForm);
            return;
        }


        console.log('tomando campos del formulario: ' + p_idForm);

        var formItems = formComponent.items;
        var formRadio = formComponent;

        console.log('Numero de items: ' + formItems.keys.length);

        for (iLoop = 0; iLoop < formItems.keys.length; iLoop++) {
            var nameElement = formItems.keys[iLoop];
            var element = Ext.getCmp(formItems.keys[iLoop]);

            console.log('elemento encontrado: ' + p_idForm + ' ' + nameElement);

            if(element === undefined){

                if(p_sufix !== undefined){
                    nameElement = nameElement + p_sufix;
                    element = Ext.getCmp(nameElement);
                    if(element === undefined){
                        console.warn('valueFormFields: Element was not found with Sufix - ' + nameElement);
                        continue;                        
                    }
                }

                console.warn('valueFormFields: Element was not found - ' + nameElement);
                continue;
            }            
            if (element.xtype === 'fieldset') {

                for (iLoopSet = 0; iLoopSet < element.items.keys.length; iLoopSet++) {
                    var field = Ext.getCmp(element.items.keys[iLoopSet]);

                    if (field.xtype === 'radiogroup') {

                        var radioGroup = Ext.getCmp(field.itemId).getValue();
                        var optionRadio = radioGroup[field.id];
                        p_objData[field.itemId] = optionRadio;
                    }
                    else {
                        p_objData[field.itemId] = field.value;
                    }
                }
            }
        }
    },

    prepareApiData: function(p_objData){
        var returnValue = JSON.stringify(p_objData);
        return returnValue;
    },    

    showAppWindow: function (p_windowId, p_title, p_maximized) {

        var winApplication = Ext.getCmp(p_windowId);
        var appUrl = "../" + p_windowId + "/index.html";
        var winMaximized = true;

        if (typeof(p_maximized) !== undefined) {
            winMaximized = p_maximized;
        }

        if (!winApplication) {
            winApplication = new Ext.Window({
                title: p_title,
                titlebar: false,
                minWidth: 1100,
                minHeight: 650,
                maximized: winMaximized,
                maximizable: false,
                draggable: false,
                closable: false,
                //ui: 'green-window',
                modal: true,
                id: p_windowId,
                itemId: p_windowId,
                layout: 'fit',
                items: [{
                    xtype: "component",
                    autoEl: {
                        tag: "iframe",
                        src: appUrl,
                        layout: 'fit'
                    }
                }],
                listeners: {
                    resize: {
                        fn: function () {
                        }
                    }
                }

            });
        }

        elf.hideScrollBars();
        winApplicationOpen();

        function winApplicationOpen() {
            winApplication.show();
        }

        function winApplicationClose() {
            winApplication.close();
        }
    },

    showScrollBars: function () {
        document.documentElement.style.overflow = 'auto';  // firefox, chrome
        document.body.scroll = "yes"; // ie only            
    },

    hideScrollBars: function () {
        document.documentElement.style.overflow = 'hidden';  // firefox, chrome
        document.body.scroll = "no"; // ie only
    },

    showRecord: function (p_jsonRecordData,
                           p_recordTable,
                           p_mode) {
        var fieldUpdated;
        var prefixObj = ['fi', 'df', 'dt', 'ch', 'cbx', 'ind', 'tf', 'ta', 'rg', 'rb'];
        var fieldForm;

        jsonObject = p_jsonRecordData[p_recordTable][0];

        if (jsonObject === undefined) {
            jsonObject = p_jsonRecordData[p_recordTable];
        }

        for (var key in jsonObject) {
            for (var prefix in prefixObj) {

                var valPrefix = prefixObj[prefix];

                fieldUpdated = valPrefix + elf.toCamelCase(key);

                fieldForm = Ext.getCmp(fieldUpdated);

                if (fieldForm !== undefined) {
                    try {
                        var fieldValue = jsonObject[key];

                        switch (fieldForm.xtype) {
                            case 'datefield':
                                fieldValue = Ext.Date.parse(fieldValue, 'c');
                                fieldValue = Ext.Date.format(fieldValue, 'Y/m/d');

                                var dateValue = new Date(fieldValue);
                                elf.writeElement(fieldUpdated, dateValue);

                                break;
                            default:
                                elf.writeElement(fieldUpdated, fieldValue);
                        }

                        elf.disableElement(fieldUpdated);

                        if (p_mode === 'edit') {
                            elf.enableElement(fieldUpdated);
                        }

                    }
                    catch (error) {
                        console.error(fieldUpdated + '(' + jsonObject[key] + ')' + ' >> ' + error.message);
                    }
                }
            } //for - Prefix
        } //for - jsonObject 
    },
    setGridParams: function(p_storeName,
                             p_zoomClause,
                             p_apiController,
                             p_apiMethod){

        var storeGrid = Ext.getStore(p_storeName);
        var proxyGrid = storeGrid.getProxy();

        var objJsonData = new Object();
        objJsonData.page  = storeGrid.currentPage;
        objJsonData.start = (storeGrid.currentPage - 1) * storeGrid.pageSize;
        objJsonData.limit = storeGrid.pageSize;

        if(p_zoomClause !== undefined){
            var clauseZoom = elf.getFieldClauseZoom(p_zoomClause);
            if(clauseZoom !== undefined){
                Object.keys(clauseZoom).forEach(function(key) {
                    var keyValue = clauseZoom[key];
                    objJsonData[key] = keyValue;
                });
            }
        }
        else{
            console.info('setGridParams: clauseZoom was not found!');
        }

        /*----------------------------*/
        /* ECRC: Adding Fields Clause */
        /*----------------------------*/
        var objJsonRequest = new Object();
        objJsonRequest.apiController = p_apiController;
        objJsonRequest.apiMethod     = p_apiMethod;
        objJsonRequest.apiData       = JSON.stringify(objJsonData);

        proxyGrid.api.read    = '../../backend/public/dataBridge/' + objJsonRequest.apiController;
        proxyGrid.extraParams = objJsonRequest;        
    },
    showZoomWindow: function (p_windowId, 
                               p_title, 
                               p_fieldClause,
                               p_fieldCode,
                               p_fieldName,
                               p_fieldFocus,
                               p_objExtraFields) {

        var winZoom = Ext.getCmp(p_windowId);
        var appUrl = "../" + p_windowId + "/index.html";
        var winMaximized = false;

        function winZoomOpen() {
            winZoom.show();
        }

        function winZoomClose() {
            winZoom.close();
        }


        function writeData(p_windowId){
            var zoomData = elf.getInfoZoom(p_windowId);
            elf.writeElement(p_fieldCode,zoomData.field_code);
            elf.writeElement(p_fieldName,zoomData.field_name);

            console.log('Escribiendo otros datos');
            console.log(p_objExtraFields);

            if(p_objExtraFields !== undefined){
                console.log('zoomData');
                console.log(zoomData);

                var aFieldForm = p_objExtraFields.fieldForm;
                var aFieldData = p_objExtraFields.fieldData;
                var fullRecord = zoomData.full_record;

                for(var iLoop = 0; iLoop < aFieldForm.length; iLoop++){
                    var fieldForm = aFieldForm[iLoop];
                    var fieldData = aFieldData[iLoop];

                    console.warn('Escribiendo Valores');
                    console.warn(fieldForm);
                    console.warn(fieldData);
                    console.warn(fullRecord[fieldData]);

                    elf.writeElement(fieldForm,fullRecord[fieldData]);

                }

            }
        }

        function defaultClose(){
            console.log('Este es el default close');
            writeData(p_windowId);
            winZoomClose(); 
        }

        if(p_fieldClause !== undefined){
            console.log('Definiendo la clausua');

            elf.setFieldClauseZoom(p_windowId,
                                   p_fieldClause);
        }

        if (!winZoom) {
            winZoom = new Ext.Window({
                title: p_title,
                titlebar: false,
                minWidth: 620,
                minHeight: 420,
                maximized: winMaximized,
                maximizable: false,
                draggable: false,
                closable: false,
                ui: 'green-window',
                modal: true,
                id: p_windowId,
                itemId: p_windowId,
                layout: 'fit',
                items: [{
                    xtype: "component",
                    autoEl: {
                        tag: "iframe",
                        src: appUrl,
                        layout: 'fit'
                    }
                }],               
                buttons: [
                    {
                        text: 'Select Record',
                        handler: function(){
                            defaultClose();
                            winZoomClose(); 

                        }
                    },

                    {
                        text: 'Close',
                        handler: function(){
                            winZoomClose(); 

                        }
                    }
                ],
                defaultClose: function(p_windowId){
                    writeData(p_windowId);
                    winZoomClose(); 
                },
                listeners: {
                    resize: {
                        fn: function () {
                        }
                    }
                }

            });
        }

        elf.hideScrollBars();
        winZoomOpen();

    },   
    setFieldClauseZoom: function(p_idStore,
                                  p_objClause){

        p_idStore = 'clause_' + p_idStore;

        Ext.define('modClauseZoom',
                   {
                       extend: 'Ext.data.Model',
                       fields: [
                           {name: 'idStore', type: 'string'},
                           {name: 'objClause', type: 'string'}
                       ]
                   });

        Ext.define('storeClauseZoom',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modClauseZoom',
                       proxy: {
                           type: 'sessionstorage',
                           id: p_idStore
                       }
                   });        

        var storeClauseZoom = Ext.create('storeClauseZoom');
        storeClauseZoom.getProxy().clear();

        storeClauseZoom.add({
            idStore: p_idStore,
            objClause: JSON.stringify(p_objClause)
        });

        storeClauseZoom.sync();

    },   
    getFieldClauseZoom: function (p_idStore) {

        p_idStore = 'clause_' + p_idStore;

        console.log('Buscando en ' + p_idStore);

        Ext.define('modClauseZoom',
                   {
                       extend: 'Ext.data.Model',
                       fields: [
                           {name: 'idStore', type: 'string'},
                           {name: 'objClause', type: 'string'}
                       ]
                   });

        Ext.define('storeClauseZoom',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modClauseZoom',
                       proxy: {
                           type: 'sessionstorage',
                           id: p_idStore
                       }
                   });

        var returnValue = '';
        var storeClauseZoom = Ext.create('storeClauseZoom');

        storeClauseZoom.load(function (records, op, success) {
            var infoClauseZoom, iLoop;

            console.log('caqrgando datos');
            console.log(records);

            for (iLoop = 0; iLoop < records.length; iLoop++) {
                infoClauseZoom = records[iLoop].data;
                returnValue = infoClauseZoom.objClause;

            }

            if(records.length === 0){
                returnValue = undefined;

            }

            return returnValue;
        });

        console.log(returnValue);

        if(returnValue !== undefined){
            returnValue = Ext.decode(returnValue);
        }

        return returnValue;
    },    

    enableElement: function (p_elemento) {
        var elemento = Ext.getCmp(p_elemento);
        if (elemento !== undefined) {
            elemento.setDisabled(false);
        }
    },

    disableElement: function (p_elemento) {
        var elemento = Ext.getCmp(p_elemento);
        if (elemento !== undefined) {
            elemento.setDisabled(true);
        }
        else {
            console.error('disableElement - No se encontró el Componente o Elemento: ' + p_elemento);
        }
    },

    toCamelCase: function (str) {
        var stringReturn;

        stringReturn = str.replace(/_/g, ' ');

        stringReturn = stringReturn.toLowerCase()
        .replace(/['"]/g, '')
        .replace(/\W+/g, ' ')
        .replace(/ (.)/g, function ($1) {
            return $1.toUpperCase();
        })
        .replace(/ /g, '')
        ;

        stringReturn = stringReturn.charAt(0).toUpperCase() + stringReturn.slice(1);
        return stringReturn;
    },

    translateInterface: function (p_interfaceName) {

        var objJsonData = new Object();

        objJsonData.interface = p_interfaceName;

        var objJsonRequest = new Object();
        objJsonRequest.apiController = 'apiPrepareInterface';
        objJsonRequest.apiMethod = 'getInformation';
        objJsonRequest.apiData = JSON.stringify(objJsonData);

        var functionSuccess = function () {

            var jsonData = elf.getInfoDataBridge('apiPrepareInterface');
            var defaultLanguage = environment.defaultLanguage();

            var languageData = jsonData.languages.filter(function (languageData) {
                return languageData.name === defaultLanguage;
            })[0];

            var translations = jsonData.translations;

            for (var iLoop = 0; iLoop < translations.length; iLoop++) {
                var tooltip = 'tooltip0' + languageData.id;
                var empty = 'empty0' + languageData.id;
                var translation = 'translation0' + languageData.id;

                var widgetId = translations[iLoop]['widget'];
                widgetObj = Ext.getCmp(widgetId);
                if (widgetObj !== undefined) {
                    console.log(widgetObj);

                    if (widgetObj.componentCls === 'x-field') {
                        widgetObj.labelEl.update(translations[iLoop][translation]);
                        widgetObj.emptyText = translations[iLoop][empty];
                        widgetObj.applyEmptyText();
                        widgetObj.reset();
                    }

                    if (widgetObj.componentCls === 'x-btn') {
                        widgetObj.setText(translations[iLoop][translation]);
                        widgetObj.setTooltip(translations[iLoop][tooltip]);

                        /* pone un mensage despues de ejecutar
                         var toolTip = Ext.get(widgetId);
                         toolTip.set({ 
                         'data-qtitle': 'New Tooltip Title', //this line is optional
                         'data-qtip': 'Updated Tool Tip!' 
                         });
                         */
                    }
                }
            }

        };

        var functionFailure = function () {
            var jsonData = elf.getInfoDataBridge('apiPrepareInterface');
            elf.showInfo(jsonData, 'error', 'tfEmail');
        };

        elf.doDataBridge(objJsonRequest,
                         functionSuccess,
                         null,
                         functionFailure,
                         null);
    },

    focusElement: function (p_element) {
        var element = Ext.getCmp(p_element);

        if (element === undefined) {
            console.error('elf::focusElement - Element was not found: ' + p_element);
            return;
        }
        element.focus(false, 200);
    },

    message: function (p_type, p_title, p_message, p_function, p_format) {

        var iconoMessage;
        var botonMessage;
        var botonTexto;
        var decodificaUtf8;

        p_type = p_type.toLowerCase();


        switch (p_type) {
            case "information":
                iconoMessage = Ext.MessageBox.INFO;
                botonMessage = Ext.Msg.OK;
                break;
            case "question":
                iconoMessage = Ext.MessageBox.QUESTION;
                botonMessage = Ext.Msg.YESNO;
                botonTexto = {
                    yes: 'Confirmar',
                    no: 'Cerrar'
                };
                break;
            case "warning":
                iconoMessage = Ext.MessageBox.WARNING;
                botonMessage = Ext.Msg.OK;
                break;
            case "error":
                iconoMessage = Ext.MessageBox.ERROR;
                botonMessage = Ext.Msg.OK;
                break;
        }

        if (p_message.indexOf("á") > 0 ||
            p_message.indexOf("é") > 0 ||
            p_message.indexOf("í") > 0 ||
            p_message.indexOf("ó") > 0 ||
            p_message.indexOf("ú") > 0 ||
            p_message.indexOf("ñ") > 0
           ) {
            decodificaUtf8 = false;
        }
        else {
            decodificaUtf8 = true;
        }

        if (decodificaUtf8 === true) {
            p_message = Ext.encode(p_message);
        }

        /*--------------------------------------\
         | ECRC: Formato especial del Message.	|
         \--------------------------------------*/
        var widthMessage = 400;
        if (p_format !== undefined && p_format.width !== undefined) {
            widthMessage = p_format.width;
        }

        Ext.Msg.show({
            title: p_title,
            msg: p_message,
            icon: iconoMessage,
            buttons: botonMessage,
            buttonText: botonTexto,
            fn: function (btn) {

                if (btn == 'yes' || btn == 'ok') {
                    if (p_function !== '' && p_function !== undefined) {

                        if (String(p_function).indexOf('function') != -1) {
                            p_function();
                        }
                        else {
                            eval(p_function);
                        }
                    }
                }
            },
            width: widthMessage
        });
    },

    showInfo: function(p_infoDataBridge,
                        p_type,
                        p_fieldFocus,
                        p_messageFunction) {

        if (p_infoDataBridge === undefined) {
            return;
        }

        console.log('showing info');
        console.log(p_infoDataBridge);

        var numRecords = Object.keys(p_infoDataBridge.message).length;
        var dataInformation = '';

        for (var iLoop = 0; iLoop < numRecords; iLoop++) {
            if (p_infoDataBridge.message[iLoop] !== null) {
                dataMessage = p_infoDataBridge.message[iLoop];

                if (dataMessage.type !== undefined && dataMessage.type == 'info-api') {
                    if(dataInformation === ''){
                        dataInformation = dataMessage.message;
                    }
                    else{
                        dataInformation = dataInformation + '</br></br>' + dataMessage.message;
                    }
                }
            }
        }

        var msgTitulo = '';
        switch (p_type) {
            case 'information':
                msgTitulo = 'System Information';
                break;
            case 'question':
                msgTitulo = 'System Question';
                break;
            case 'warning':
                msgTitulo = 'System Warning';
                break;
            case 'error':
                msgTitulo = 'System Error';
                break;
            default:
        }

        var msgTipo = p_type;
        var msgContenido = dataInformation;
        var msgFuncion = function () {
            if (p_fieldFocus !== undefined) {
                elf.focusElement(p_fieldFocus);
            }

            if (p_messageFunction !== undefined) {
                console.log('vba a ejecutar una funcion extra');
                console.log(p_messageFunction);

                p_messageFunction();
            }
        };

        var msgFormato = {};
        msgFormato.width = 500;

        this.message(msgTipo,
                     msgTitulo,
                     msgContenido,
                     msgFuncion,
                     msgFormato
                    );
    },

    writeElement: function (p_elemento,
                             p_valor,
                             p_tipo) {

        var valorElemento = p_valor;
        var elemento = Ext.getCmp(p_elemento);

        if (elemento === undefined) {
            console.error('writeElement - No se encontró el Componente o Elemento: ' + p_elemento);
            return;
        }

        if (p_valor === 'NULL' ||
            p_valor === undefined ||
            p_valor === null) {
            p_valor = '';
        }

        if (p_valor !== '' || p_valor !== null) {
            if (elemento.xtype !== 'datefield') {
                if (p_valor !== undefined) {

                    p_valor = Ext.util.Format.htmlDecode(p_valor);
                }
            }
        }

        if (elemento !== undefined) {
            switch (elemento.xtype) {
                case 'textfield':
                    elemento.setValue(p_valor);
                    break;
                case 'tbtext':
                    elemento.update(p_valor);
                    break;
                case 'label':
                    elemento.update(p_valor);
                    break;
                case 'checkboxfield':
                    if (p_valor === 'true' || p_valor === 'yes') {
                        elemento.setValue(true);
                    }
                    else {
                        elemento.setValue(false);
                    }
                    break;
                case 'radiogroup':
                    var idRadioChecked = 'rb' + p_valor;
                    Ext.getCmp(idRadioChecked).setValue(true);
                    break;
                case 'combobox':
                    if(p_valor.indexOf(',') !== 0){
                        var arrayValor = p_valor.split(',');
                        elemento.setValue(arrayValor);
                    }
                    else{
                        elemento.setValue(p_valor);
                    }
                    break;
                default:
                    elemento.setValue(p_valor);
            }

            if (isNaN(p_valor) === false) {
                if (p_tipo == "INT") {
                    Ext.getCmp(p_elemento).setValue(Ext.util.Format.number(p_valor, '0,000'));
                }

                if (p_tipo == "DEC") {
                    Ext.getCmp(p_elemento).setValue(Ext.util.Format.number(p_valor, '0,000.00'));
                }

                if (p_tipo == "VAL") {
                    Ext.getCmp(p_elemento).setValue(Ext.util.Format.number(p_valor, '0,000.00000'));
                }

                if (Ext.getCmp(p_elemento).fieldCls === 'formatDecimal'){
                    Ext.getCmp(p_elemento).setValue(Ext.util.Format.number(p_valor, '0,000.00'));
                }

                if (Ext.getCmp(p_elemento).fieldCls === 'formatInteger'){
                    Ext.getCmp(p_elemento).setValue(Ext.util.Format.number(p_valor, '0,000'));
                }
            }

            /*--------------------------------------------------\
             | ECRC: Asignación de Valores para Campos Fecha.	|
             \--------------------------------------------------*/
            var elementoDate = Ext.getCmp(p_elemento);

            if(p_elemento === 'tfFechaVenta'){
                console.warn(p_elemento);
                console.warn(elementoDate);
            }

            if (elementoDate.xtype === 'datefield') {
                if (p_valor !== 'NULL' &&
                    p_valor !== '' &&
                    p_valor !== null &&
                    p_valor !== undefined) {

                    console.warn('va a siganr una FEcha: ' + p_valor);

                    var timestamp=Date.parse(p_valor);
                    if (isNaN(timestamp) === false){
                        if (typeof(p_valor) == 'object') {
                            console.warn('asignmo aki');
                            elementoDate.setValue(p_valor);
                        }
                        else {
                            var valFecha = new Date(p_valor + 'T00:00:00-06:00');

                            console.warn('asignmo aka');
                            elementoDate.setValue(valFecha);
                        }

                    }
                    else{
                        console.warn('Nuleado la Fecha');
                        elementoDate.setValue(null);
                    }
                }
                else {
                    elementoDate.setValue(null);
                }
            }
        }
        else {
            var mensaje = 'No se encontro el Elemento con Id  o itemId en la Interfaz: ' + p_elemento;
            console.error(mensaje);
        }

    },
    readElement: function (p_element, p_type) {
        var returnValue = "";
        var Elemento = Ext.getCmp(p_element);

        if (Elemento === undefined) {
            console.error('readElement - Element was not found by ID: ' + p_element);
            return;
        }

        var tipoElemento = Elemento.getXType();
        switch (tipoElemento) {
            case 'datefield':
                console.warn('Campo Fecha');
                returnValue = Ext.getCmp(p_element).getSubmitValue();
                console.warn(returnValue);

                if (p_type === 'date') {
                    var dia = returnValue.substring(0, 2);
                    var mes = returnValue.substring(3, 5);
                    var ann = returnValue.substring(8, 10);

                    returnValue = dia + "-" + mes + "-" + ann;
                }

                console.warn(returnValue);
                break;
            case 'timefield':
                returnValue = Ext.getCmp(p_element).getValue();
                returnValue = Ext.Date.format(returnValue, 'H:i');
                returnValue = returnValue.replace(':', '|');
                break;
            case 'radiogroup':
                var radioGroup = Ext.getCmp(p_element).getValue();
                returnValue = radioGroup[p_element];
                break;
            case 'checkbox':
                returnValue = Ext.getCmp(p_element).getValue().toString();
                break;
            case 'combo':
                if(Array.isArray(Ext.getCmp(p_element).getValue())){
                    comboBoxValue = Ext.getCmp(p_element).getRawValue();
                    comboBoxValue = comboBoxValue.replace(/\s+/g, '');
                }
                else{
                    comboBoxValue = Ext.getCmp(p_element).getValue();
                }

                returnValue = comboBoxValue;
                break;
            default:
                var element = Ext.getCmp(p_element);
                if(element !== undefined){
                    try{
                        returnValue = element.getValue();
                    }
                    catch(err){

                        var errorFound = 'elf::readElement: Error assigning value > ' + p_element +
                            ' Error: ' + err.message;
                        console.warn(errorFound);
                    }

                }
                else{
                    console.warn('elf::readElement: Element was not found > ' + p_element);
                }

        }

        if (p_type === 'number') {
            /* ECRC: Cleaning the number format  */
            returnValue = returnValue.replace("$", "");
            returnValue = returnValue.split(",").join('');
            returnValue = Number(returnValue);
        }

        if (typeof returnValue == 'string') {
            returnValue = returnValue.trim();
        }
        return returnValue;
    },

    carga: function () {
        alert('cargado');
        console.log('funcion de carga');
    },

    setInfoZoom: function(p_idStore,
                           p_objData){

        Ext.define('modDataZoom',
                   {
                       extend: 'Ext.data.Model',
                       fields: [
                           {name: 'idStore', type: 'string'},
                           {name: 'objData', type: 'string'}
                       ]
                   });

        Ext.define('storeDataZoom',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modDataZoom',
                       proxy: {
                           type: 'sessionstorage',
                           id: p_idStore
                       }
                   });        

        var storeDataZoom = Ext.create('storeDataZoom');
        storeDataZoom.getProxy().clear();

        storeDataZoom.add({
            idStore: p_idStore,
            objData: JSON.stringify(p_objData)
        });

        storeDataZoom.sync();

    },
    getInfoZoom: function (p_idStore) {

        Ext.define('modDataZoom',
                   {
                       extend: 'Ext.data.Model',
                       fields: [
                           {name: 'idStore', type: 'string'},
                           {name: 'objData', type: 'string'}
                       ]
                   });

        Ext.define('storeDataZoom',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modDataZoom',
                       proxy: {
                           type: 'sessionstorage',
                           id: p_idStore
                       }
                   });

        var returnValue = '';
        var storeDataZoom = Ext.create('storeDataZoom');

        storeDataZoom.load(function (records, op, success) {
            var infoDataZoom, iLoop;

            console.log('caqrgando datos');
            console.log(records);

            for (iLoop = 0; iLoop < records.length; iLoop++) {
                infoDataZoom = records[iLoop].data;
                returnValue = infoDataZoom.objData;

            }

            return returnValue;
        });

        console.log(returnValue);

        returnValue = Ext.decode(returnValue);
        return returnValue;
    },

    setInfoDataBridge: function (p_idStore,
                                  p_respuestaJson) {

        Ext.define('modDataBridge',
                   {
                       extend: 'Ext.data.Model',
                       fields: [
                           {name: 'idStore', type: 'string'},
                           {name: 'respuestaJson', type: 'string'}
                       ]
                   });

        Ext.define('storeDataBridge',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modDataBridge',
                       proxy: {
                           type: 'sessionstorage',
                           id: p_idStore
                       }
                   });

        var storeDataBridge = Ext.create('storeDataBridge');
        storeDataBridge.getProxy().clear();

        storeDataBridge.add({
            idStore: p_idStore,
            respuestaJson: p_respuestaJson
        });

        storeDataBridge.sync();
    },

    getInfoDataBridge: function (p_idStore) {

        Ext.define('modDataBridge',
                   {
                       extend: 'Ext.data.Model',
                       fields: [
                           {name: 'idStore', type: 'string'},
                           {name: 'respuestaJson', type: 'string'}
                       ]
                   });

        Ext.define('storeDataBridge',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modDataBridge',
                       proxy: {
                           type: 'sessionstorage',
                           id: p_idStore
                       }
                   });

        var returnValue = '';
        var storeDataBridge = Ext.create('storeDataBridge');

        Ext.define('storeDataBridge',
                   {
                       extend: 'Ext.data.Store',
                       model: 'modDataBridge',
                       proxy: {
                           type: 'sessionstorage',
                           id: 'openLink'
                       }
                   });

        storeDataBridge.load(function (records, op, success) {
            var infoDataBridge, iLoop;

            for (iLoop = 0; iLoop < records.length; iLoop++) {
                infoDataBridge = records[iLoop].data;
                //returnValue = Ext.decode(infoDataBridge.respuestaJson);
                returnValue = infoDataBridge.respuestaJson;

            }

            return returnValue;
        });

        returnValue = Ext.decode(returnValue);
        return returnValue;
    },

    packData: function(objJsonData){
        var apiData = '';

        //ECRC: Adding default fields
        objJsonData.company_code   = elf.getSessionData('company_code');
        objJsonData.company_name   = elf.getSessionData('company_name');
        objJsonData.user_id        = elf.getSessionData('user_id');
        objJsonData.user_name      = elf.getSessionData('user_name');
        objJsonData.user_profile   = elf.getSessionData('user_profil');
        objJsonData.user_email     = elf.getSessionData('user_email');
        objJsonData.profile_code   = elf.getSessionData('profile_code');
        objJsonData.system_code    = elf.getSessionData('system_code');
        objJsonData.system_name    = elf.getSessionData('system_name');
        objJsonData.system_version = elf.getSessionData('system_version');

        apiData = JSON.stringify(objJsonData);
        return apiData;
    },

    doDataBridge: function (p_jsonDataForm,
                             p_fncSuccess,
                             p_paramsSuccess,
                             p_fncFailure,
                             p_paramsFailure,
                             p_fncCallback,
                             p_paramsCallback,
                             pDebugMode) {


        var funcionSuccess;
        var funcionFailure;

        if(p_jsonDataForm.noMessage === undefined){
            var boxWait = Ext.MessageBox.show({
                msg: 'Por favor espere, procesando informacion...',
                progressText: 'Procesando',
                width: 300,
                wait: true,
                waitConfig: {interval: 170}
            });
        }

        var nombreCampo;
        var valorCampo;
        var aDatosJson;
        aDatosJson = new Array();
        aDatosJson[0] = new Object();

        var tokenLaravel = Ext.util.Cookies.get('XSRF-TOKEN');
        if (tokenLaravel !== null) {
            p_jsonDataForm._token = tokenLaravel;
        }

        p_jsonDataForm.defaultLanguage = environment.defaultLanguage();

        var urlAjax = '../../controlador/' + p_jsonDataForm.apiController + '.php';


        Ext.Ajax.request({
            url: urlAjax,
            method: 'POST',
            headers: {
                'XSRF-TOKEN': tokenLaravel
            },
            params: p_jsonDataForm,
            success: function (response, opts) {
                var jsonData = Ext.decode(response.responseText);
                var idDataBridge = p_jsonDataForm.apiMethod;

                if (p_jsonDataForm.apiController !== undefined) {
                    idDataBridge = p_jsonDataForm.apiMethod;
                }

                elf.setInfoDataBridge(idDataBridge,
                                      response.responseText
                                     );

                /* ECRC: Función cuando se ejecuta correctamente el Script en el Servidor */
                if (jsonData.success === true) {
                    if(p_jsonDataForm.noMessage === undefined){
                        boxWait.hide();
                    }

                    if (p_fncSuccess !== undefined) {
                        /*--------------------------------------\
                         | ECRC: Integración de Jasper Reports.	|
                         \--------------------------------------*/
                        if (p_fncSuccess == "generaReporte") {
                            /*----------------------------------------------------------------------\
                             | ECRC: En ésta sección se general la URL para  presentar  el  Reporte	|
                             |       para que posteriormente se presente una Ventana con el Reporte	|
                             |       ya generado.													|
                             |       Los parámetros que se envían al Jasper Server son:				|
                             |       idSesion y jsonData (Con el ID Sesión y el ID Trans).			|
                             \----------------------------------------------------------------------*/

                            var urlJasper = creaUrlJasper(p_paramsSuccess);
                            funcionSuccess = p_fncSuccess + "('" + urlJasper + "',p_paramsSuccess)";

                            try {
                                eval(funcionSuccess);
                            } catch (error) {
                                console.error('Error en la Funcion: ' + funcionSuccess);
                                console.error(error.message);
                                console.error('Verifique la Sintaxis de la Funcion.');
                            }
                        }
                        else {

                            if (p_paramsSuccess === "" || p_paramsSuccess === undefined) {
                                funcionSuccess = p_fncSuccess + "(p_jsonDataForm,jsonData)";
                            }
                            else {
                                var strParametros = String(p_paramsSuccess);
                                var arrayParametros = strParametros.split(",");

                                if (arrayParametros.length > 1) {
                                    funcionSuccess = p_fncSuccess + "(";

                                    for (var iLoop = 0; iLoop <= arrayParametros.length; iLoop++) {

                                        if (arrayParametros[iLoop] !== undefined) {
                                            funcionSuccess += arrayParametros[iLoop];
                                            if (iLoop != (arrayParametros.length - 1)) {
                                                funcionSuccess += ",";
                                            }
                                        }
                                    }
                                    funcionSuccess += ")";
                                }
                                else {
                                    funcionSuccess = p_fncSuccess + "(p_paramsSuccess)";
                                }
                            }

                            try {
                                if (String(p_fncSuccess).indexOf('function') != -1) {
                                    funcionSuccess = p_fncSuccess;
                                    funcionSuccess();
                                } else {
                                    eval(funcionSuccess);
                                }

                            } catch (error) {
                                console.error('Error en la Funcion: ' + funcionSuccess);
                                console.error(error.message);
                                console.error('Verifique la Sintaxis de la Funcion.');
                            }
                        }

                    }
                }
                else {
                    if (jsonData.dsRetorno != undefined) {
                        /*------------------------------------------------------------------\
                         | ECRC: Errores de Lógica de Negocio encontrados en la ejecución	|
                         |       de los Procedimientos Backend.								|
                         \------------------------------------------------------------------*/
                        numRecords = Object.keys(jsonData.dsRetorno.ttInformacion).length;

                        for (var iLoop = 0; iLoop <= numRecords; iLoop++) {
                            if (jsonData.dsRetorno.ttInformacion[iLoop] != null) {

                                if (jsonData.dsRetorno.ttInformacion[iLoop].tipo == 'ERROR') {
                                    mensajeError = '<font color=darkred size=2px><b>' + jsonData.dsRetorno.ttInformacion[iLoop].codInformacion + ' - ';
                                    mensajeError += jsonData.dsRetorno.ttInformacion[iLoop].descInformacion + '</b></br>';
                                    mensajeError += '<i>' + jsonData.dsRetorno.ttInformacion[iLoop].adicional + '</i></font></br>';
                                }
                            }
                        }
                    }

                    if(p_jsonDataForm.noMessage === undefined){
                        boxWait.hide();
                    }

                    if (pDebugMode) {
                        Ext.Msg.show({
                            title: 'Error en API Backend',
                            msg: mensajeError,
                            width: 500,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });
                    }


                    if (p_paramsFailure === "" || p_paramsFailure === undefined) {
                        p_paramsFailure = jsonData;
                        funcionFailure = p_fncFailure + "(p_jsonDataForm,p_paramsFailure)";
                    }
                    else {

                        funcionFailure = p_fncFailure + "(p_paramsFailure,p_jsonDataForm)";
                    }


                    if (p_fncFailure !== undefined) {
                        if (String(p_fncFailure).indexOf('function') != -1) {
                            funcionFailure = p_fncFailure;
                            funcionFailure();
                        }
                        else {
                            console.info('Funcion de error: ' + p_fncFailure + ' > ' + funcionFailure);
                            console.info('Ejecutando la Funcion: ' + funcionFailure);
                            eval(funcionFailure);
                        }
                    }
                }
            },
            failure: function (response, opts) {
                /* ECRC: Función cuando hay Error en la Ejecución en el Servidor */
                console.error('elf::doDataBridse:Server fail with the Status: ' + response.status);
                if(p_jsonDataForm.noMessage === undefined){
                    boxWait.hide();
                }
            },
            callback: function (success) {
                var funcionCallback;
                if (success && p_fncCallback !== undefined) {
                    if (p_paramsCallback !== undefined || p_paramsCallback != '') {
                        funcionCallback = p_fncCallback + '(p_paramsCallback)';
                    }
                    else {
                        funcionCallback = p_fncCallback + '()';
                    }

                    try {
                        if (String(p_fncCallback).indexOf('function') != -1) {
                            funcionCallback = p_fncCallback;
                            funcionCallback();
                        } else {
                            eval(funcionCallback);
                        }

                    } catch (error) {
                        console.error('Error en la Funcion Callback');
                        console.error(error.message);
                        console.error('Verifique la Sintaxis de la Funcion');
                    }
                }
            }

        });
    }
};







