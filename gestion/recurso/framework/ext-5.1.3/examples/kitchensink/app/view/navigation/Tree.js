Ext.define('KitchenSink.view.navigation.Tree', {
    extend: 'Ext.tree.Panel',

    xtype: 'navigation-tree',

    title: 'Examples',
    rootVisible: false,
    lines: false,
    useArrows: true,
    hideHeaders: true,
    collapseFirst: false,
    width: 250,
    minWidth: 100,
    height: 200,
    split: true,
    stateful: true,
    stateId: 'mainnav.west',
    collapsible: true,
    enableColumnResize: false,
    enableColumnMove: false,
    
    bufferedRenderer: !Ext.platformTags.test,

    tools: [{
        type: 'up',
        tooltip: 'Switch to Breadcrumb View',
        listeners: {
            click: 'showBreadcrumbNav'
        }
    }],

    initComponent: function() {
        var me = this,
            lastFilterValue;

        me.columns = [{
            xtype: 'treecolumn',
            flex: 1,
            dataIndex: 'text',
            scope: me,
            renderer: function(value) {
                var searchString = this.searchField.getValue();

                if (searchString.length > 0) {
                    return this.strMarkRedPlus(searchString, value);
                }

                return value;
            }
        }];

        Ext.apply(me, {
            store: Ext.StoreMgr.get('navigation'),
            dockedItems : [
                {
                    xtype: 'textfield',
                    dock: 'top',
                    emptyText: 'Search',
                    enableKeyEvents: true,

                    triggers: {
                        clear: {
                            cls: 'x-form-clear-trigger',
                            handler: 'onClearTriggerClick',
                            hidden: true,
                            scope: 'this'
                        },
                        search: {
                            cls: 'x-form-search-trigger',
                            weight: 1,
                            handler: 'onSearchTriggerClick',
                            scope: 'this'

                        }
                    },

                    onClearTriggerClick: function() {
                        this.setValue();
                        me.store.clearFilter();
                        this.getTrigger('clear').hide();
                    },

                    onSearchTriggerClick: function() {
                        me.filterStore(this.getValue());
                    },

                    listeners: {
                        keyup: {
                            fn: function(field, event, eOpts) {
                                var value = field.getValue();

                                // Only filter if they actually changed the field value.
                                // Otherwise the view refreshes and scrolls to top.
                                if (value && value !== lastFilterValue) {
                                    field.getTrigger('clear')[(value.length > 0) ? 'show' : 'hide']();
                                    me.filterStore(value);
                                    lastFilterValue = value;
                                } else if (!value) {
                                    me.store.clearFilter();
                                }
                            },
                            buffer: 300
                        },

                        render: function(field) {
                            this.searchField = field;
                        },

                        scope: me
                    }
                }
            ]
        });

        me.callParent(arguments);
    },

    filterStore: function(value) {
        var me = this,
            store = me.store,
            filterFn = function(node) {
                var children = node.childNodes,
                    len      = children && children.length,
                    visible  = v.test(node.get('text')),
                    i;

                if (!visible) {
                    for (i=0; i<len; i++) {
                        if (children[i].isLeaf()) {
                            visible = v.test(children[i].get('text'));
                        } else {
                            visible = filterFn(children[i]);
                        }
                        if (visible) {
                            break;
                        }
                    }
                }

                return visible;

                
            }, v;

        if (!value.length) {
            store.clearFilter();
        } else {
            v = new RegExp(Ext.String.escapeRegex(value), 'i');
            store.getFilters().replaceAll({
                filterFn: filterFn
            });
        }
    },

    strMarkRedPlus: function (search, subject) {
        return subject.replace(
            new RegExp( '('+search+')', "gi" ),
            "<span style='color: red;'><b>$1</b></span>"
        );
    }
});
