Ext.define('ChartsKitchenSink.view.charts.draw.Sencha', {
    extend: 'Ext.panel.Panel',
    xtype: 'sencha-draw',
    title: 'Sencha Logo',
    
    // <example>
    exampleDescription: [
        'In this example, we display the Sencha logo as a series of paths with 3 multi-stop gradients. The logo was created with a drawing tool and exported to SVG, which we used for the pathing. Note that because we use vectors to define the image, it is scalable to any size and is fully resolution independent.'
    ],
    
    layout: {
        type: 'hbox',
        pack: 'center'
    },
    // </example>
    
    items: [{
        xtype: 'draw',
        width: 300,
        height: 300,
        resizable: {
            dynamic: true,
            pinned: true,
            handles: 'all'
        },
        gradients: [{
            id: 'grad1',
            angle: 100,
            stops: {
                0: {
                    color: '#AACE36'
                },
                100: {
                    color: '#2FA042'
                }
            }
        }, {
            id: 'grad2',
            angle: 21,
            stops: {
                0: {
                    color: '#79A933'
                },
                13: {
                    color: '#70A333'
                },
                34: {
                    color: '#559332'
                },
                58: {
                    color: '#277B2F'
                },
                86: {
                    color: '#005F27'
                },
                100: {
                    color: '#005020'
                }
            }
        }, {
            id: 'grad3',
            angle: 55,
            stops: {
                0: {
                    color: '#79AB35'
                },
                53: {
                    color: '#7CBA3D'
                },
                100: {
                    color: '#00AA4B'
                }
            }
        }],
        items: [{
            type: 'path',
            path: ['M0,109.718c0-43.13,24.815-80.463,60.955-98.499L82.914,0C68.122,7.85,58.046,23.406,58.046,41.316',
            'c0,9.64,2.916,18.597,7.915,26.039c-7.44,18.621-11.77,37.728-13.228,56.742c-9.408,4.755-20.023,7.423-31.203,7.424',
            'c-1.074,0-2.151-0.025-3.235-0.075c-5.778-0.263-11.359-1.229-16.665-2.804L0,109.718z M157.473,285.498c0-0.015,0-0.031,0-0.047',
            'C157.473,285.467,157.473,285.482,157.473,285.498 M157.473,285.55c0-0.014,0-0.027,0-0.04',
            'C157.473,285.523,157.473,285.536,157.473,285.55 M157.472,285.604c0-0.015,0.001-0.031,0.001-0.046',
            'C157.473,285.574,157.472,285.588,157.472,285.604 M157.472,285.653c0-0.012,0-0.024,0-0.037',
            'C157.472,285.628,157.472,285.641,157.472,285.653 M157.472,285.708c0-0.015,0-0.028,0-0.045',
            'C157.472,285.68,157.472,285.694,157.472,285.708 M157.472,285.756c0-0.012,0-0.023,0-0.034',
            'C157.472,285.733,157.472,285.745,157.472,285.756 M157.471,285.814c0-0.014,0-0.028,0.001-0.042',
            'C157.471,285.785,157.471,285.8,157.471,285.814 M157.471,285.858c0-0.008,0-0.017,0-0.026',
            'C157.471,285.841,157.471,285.85,157.471,285.858 M157.47,285.907c0.001-0.008,0.001-0.018,0.001-0.026',
            'C157.471,285.889,157.471,285.898,157.47,285.907 M157.47,285.964c0-0.009,0-0.017,0-0.023',
            'C157.47,285.949,157.47,285.955,157.47,285.964 M157.469,286.01c0-0.008,0.001-0.016,0.001-0.022',
            'C157.47,285.995,157.469,286.002,157.469,286.01 M157.469,286.069c0-0.008,0-0.016,0-0.022',
            'C157.469,286.053,157.469,286.062,157.469,286.069 M157.468,286.112c0-0.005,0-0.011,0-0.017',
            'C157.468,286.101,157.468,286.107,157.468,286.112 M157.467,286.214c0-0.003,0-0.006,0-0.008',
            'C157.467,286.208,157.467,286.212,157.467,286.214'].join(''),
            fill: '#C5D83E'
        }, {
            type: 'path',
            path: ['M66.218,210.846l-6.824-3.421c-0.016-0.009-0.033-0.018-0.048-0.025',
            'c-0.006-0.003-0.013-0.007-0.019-0.01c-0.01-0.005-0.017-0.009-0.028-0.015c-0.009-0.005-0.016-0.008-0.025-0.013',
            'c-0.008-0.005-0.012-0.007-0.021-0.011c-0.009-0.005-0.018-0.01-0.027-0.014c-0.007-0.005-0.013-0.008-0.02-0.012',
            'c-0.009-0.005-0.02-0.01-0.029-0.015c-0.006-0.003-0.007-0.004-0.014-0.007c-0.038-0.021-0.074-0.039-0.113-0.06',
            'c-0.002-0.001-0.006-0.003-0.008-0.005c-0.013-0.006-0.023-0.011-0.035-0.018c-0.005-0.002-0.007-0.003-0.011-0.006',
            'c-0.011-0.005-0.025-0.014-0.036-0.02c-0.004-0.002-0.005-0.002-0.009-0.004c-0.013-0.007-0.025-0.014-0.038-0.02l-0.003-0.002',
            'c-29.686-15.598-51.36-44.362-57.28-78.53c5.306,1.575,10.887,2.541,16.665,2.804c1.084,0.05,2.161,0.075,3.235,0.075',
            'c11.18-0.001,21.795-2.669,31.203-7.424C50.44,154.002,55.248,183.676,66.218,210.846'].join(''),
            fill: 'url(#grad1)'
        }, {
            type: 'path',
            path: ['M88.093,85.247l-3.657-1.834c-0.214-0.103-0.426-0.208-0.638-0.315h-0.001',
            'c-0.015-0.008-0.029-0.015-0.044-0.022l-0.001-0.001c-0.014-0.007-0.028-0.014-0.042-0.021c-0.001-0.001-0.003-0.002-0.004-0.002',
            'c-0.014-0.007-0.027-0.014-0.04-0.02c-0.003-0.002-0.003-0.002-0.006-0.004c-0.013-0.006-0.025-0.012-0.037-0.018',
            'c-0.003-0.002-0.006-0.004-0.009-0.005c-0.011-0.006-0.022-0.011-0.033-0.017c-0.004-0.002-0.008-0.004-0.013-0.006',
            'c-0.009-0.005-0.018-0.01-0.027-0.014c-0.006-0.003-0.013-0.007-0.018-0.01c-0.006-0.003-0.013-0.006-0.019-0.009',
            'c-0.01-0.005-0.018-0.009-0.027-0.014c-0.001-0.001-0.003-0.002-0.004-0.002c-7.075-3.631-13.103-9.016-17.512-15.578',
            'c-7.44,18.621-11.77,37.728-13.228,56.742c12.607-6.37,23.053-16.485,29.815-28.949L88.093,85.247z M213.364,195.358',
            'c-25.889,17.124-56.849,27.05-89.924,27.05c-2.519,0-5.05-0.057-7.591-0.174c-14.436-0.662-28.343-3.192-41.515-7.32l56.748,28.445',
            'c15.615,7.571,26.39,23.571,26.39,42.092v0.107c0,0.015-0.001,0.031-0.001,0.046v0.168c-0.001,0.014-0.001,0.028-0.001,0.042v0.066',
            'c0,0.009,0,0.019-0.001,0.026v0.081c0,0.007-0.001,0.015-0.001,0.022v0.059c0,0.009-0.001,0.019-0.001,0.026v0.017',
            'c0,0.032-0.001,0.063-0.001,0.095v0.008c-0.192,12.063-4.956,23.016-12.633,31.202c-3.517,3.753-7.647,6.924-12.23,9.355',
            'l14.101-7.202l7.859-4.011c36.137-18.041,60.955-55.376,60.955-98.509L213.364,195.358z'].join(''),
            fill: 'url(#grad2)'
        }, {
            type: 'path',
            path: ['M123.44,222.408c-2.519,0-5.05-0.057-7.591-0.174c-14.436-0.662-28.343-3.192-41.515-7.32',
            'l-8.117-4.067c-10.97-27.17-15.778-56.844-13.485-86.749c12.607-6.37,23.053-16.485,29.815-28.949l5.545-9.901l68.032,34.101',
            'c2.462,1.278,4.871,2.648,7.22,4.102c0.006,0.004,0.009,0.006,0.016,0.01c0.009,0.005,0.018,0.011,0.025,0.016',
            'c0.009,0.005,0.02,0.011,0.028,0.017c0.002,0.001,0.008,0.005,0.01,0.006c25.392,15.756,43.88,41.564,49.94,71.859',
            'C187.476,212.482,156.516,222.408,123.44,222.408'].join(''),
            fill: 'url(#grad3)'
        }]
    }]
});
