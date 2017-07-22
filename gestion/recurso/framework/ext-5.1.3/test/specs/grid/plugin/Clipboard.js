/* global Ext, MockAjaxManager, expect, jasmine, spyOn, xit */

describe('Ext.grid.plugin.Clipboard', function () {
    var store, cellediting, clipboard, grid, view, navModel, record, column, field;

    function makeGrid(editorCfg, clipboardCfg, gridCfg, storeCfg, locked) {
        store = new Ext.data.Store(Ext.apply({
            fields: ['name', 'email', 'phone'],
            data: [
                {'name': 'Lisa', 'email': 'lisa@simpsons.com', 'phone': '555-111-1224', 'age': 14},
                {'name': 'Bart', 'email': 'bart@simpsons.com', 'phone': '555-222-1234', 'age': 12},
                {'name': 'Homer', 'email': 'homer@simpsons.com', 'phone': '555-222-1244', 'age': 44},
                {'name': 'Marge', 'email': 'marge@simpsons.com', 'phone': '555-222-1254', 'age': 41}
            ],
            autoDestroy: true
        }, storeCfg));

        cellediting = new Ext.grid.plugin.CellEditing(editorCfg);
        clipboard = new Ext.grid.plugin.Clipboard(clipboardCfg);

        grid = new Ext.grid.Panel(Ext.apply({
            columns: [
                {header: 'Name',  dataIndex: 'name', editor: 'textfield', locked: locked},
                {header: 'Email', dataIndex: 'email', flex:1,
                    editor: {
                        xtype: 'textfield',
                        allowBlank: false
                    }
                },
                {header: 'Phone', dataIndex: 'phone', editor: 'textfield'},
                {header: 'Age', dataIndex: 'age', editor: 'textfield'}
            ],
            store: store,
            selModel: 'spreadsheet',
            plugins: [cellediting,clipboard],
            width: 400,
            height: 400,
            renderTo: Ext.getBody()
        }, gridCfg));

        view = grid.view;
        navModel = grid.getNavigationModel();
    }

    function startEdit(recId, colId) {
        record = store.getAt(recId || 0);
        column = grid.columns[colId || 0];
        cellediting.startEdit(record, column);
        field = column.field;
    }

    function clipboardAction(eventName) {
        var key;

        switch (eventName) {
            case "copy" : 
                key = 67;
                break;
            case "paste" :
                key = 86;
                break;
            case "cut" : 
                key = 88;
                break;
        }

        jasmine.fireKeyEvent(clipboard.getTarget(grid), 'keydown', key, /*shift*/ null, /*ctrl*/ true);
    }


    beforeEach(function () {
        MockAjaxManager.addMethods();
    });

    afterEach(function () {
        MockAjaxManager.removeMethods();

        if (grid) {
            Ext.destroy(grid);
        }

        tearDown();
    });

    function tearDown() {
        store = cellediting = clipboard = grid = view = navModel = record = column = field = Ext.destroy(grid);
    }

    describe("System clipboard and plugin interaction", function() {
        beforeEach(function() {
            makeGrid({pluginId:'test-cell-editing'});
        });

        it("system clipboard should take precedence when grid is being edited", function() {
            var field;

            spyOn(clipboard, 'validateAction').andCallThrough();

            startEdit(0,0);
            field = cellediting.activeEditor.field;
            field.selectText();

            clipboardAction("copy");
            clipboardAction("cut");
            field.setValue('Foo');
            clipboardAction("paste");

            // here we are testing the validateAction method because it is the best
            // way of testing that the clipboard plugin did not disturb the system's
            // clipboard action.
            expect(clipboard.validateAction.callCount).toBe(3);
            expect(clipboard.getHiddenTextArea().dom.value).toBe('');
            expect(field.getValue()).toBe('Foo');
        });
    });
});