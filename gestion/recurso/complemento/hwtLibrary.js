var hwtLibrary = {
    calculaAnnUnidad: function (pVin, pJsonData) {

        //ECRC: Calculando el Año de acuerdo con el VIN
        var digitoAnn = pVin.substring(10, 11);
        var valorAnn = '';

        var jsonData = pJsonData;
        var parametrosCalculoAnn = jsonData.parametrosCalculoAnn;

        console.warn(parametrosCalculoAnn);


        for (var iCiclo = 0; iCiclo < Object.keys(parametrosCalculoAnn).length; iCiclo++) {
            if (parametrosCalculoAnn[iCiclo] !== null) {
                var arrayOpciones = parametrosCalculoAnn[iCiclo][0].value.split('|');
                for (var iBuscaAnn = 0; iBuscaAnn < arrayOpciones.length; iBuscaAnn++) {
                    var codigoAnn = arrayOpciones[iBuscaAnn].substring(0, 1);

                    if (digitoAnn === codigoAnn) {
                        valorAnn = arrayOpciones[iBuscaAnn].substring(2);
                    }
                }
            }
        }

        if (valorAnn === '') {
            var tiempoActual = new Date();
            var annActual = tiempoActual.getFullYear();

            valorAnn = annActual;
        }

        console.warn('Valor Retornado: ' + valorAnn);
        return valorAnn;
    }
};