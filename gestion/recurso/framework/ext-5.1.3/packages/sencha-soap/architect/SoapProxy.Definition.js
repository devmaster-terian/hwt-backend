{
    "classAlias" : "proxy.soap",
    "className"  : "Ext.data.soap.Proxy",
    "inherits"   : "Ext.data.proxy.Ajax",
    "autoName"   : "SoapProxy",
    "helpText"   : "A Soap Proxy",

    "toolbox" : {
        "name"     : "Soap Proxy",
        "category" : "Data Proxies",
        "groups"   : ["Data"]
    },

    "configs": [{
        "name":"operationParam",
        "type":"string",
        "initialValue":"op"
    },{
        "name":"reader",
        "type":"string",
        "initialValue":"soap"
    },{
        "name":"url",
        "type":"string",
        "initialValue":""
    },{
        "name":"targetNamespace",
        "type":"string",
        "initialValue":""
    }]
}