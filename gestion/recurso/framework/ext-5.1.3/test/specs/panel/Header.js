describe("Ext.panel.Header", function() {
    var header;
    
    function makeHeader(cfg) {
        cfg = Ext.apply({
            title: 'foo',
            renderTo: Ext.getBody()
        }, cfg);
        
        return header = new Ext.panel.Header(cfg);
    }

    afterEach(function() {
        Ext.destroy(header);
        header = null;
    });

    describe('Title value', function() {
        it('should set it as configured', function() {
            makeHeader({
                title: 10
            });
            expect(header.title.getText()).toBe(10);
        });
    });

    describe("setTitlePosition", function() {
        beforeEach(function() {
            makeHeader({
                title: 'foo',
                renderTo: document.body,
                tools: [
                    { type: 'close' },
                    { type: 'pin' }
                ]
            });
        });

        it("should insert the header at the new title position", function() {
            header.setTitlePosition(2);
            expect(header.items.getAt(2)).toBe(header.getTitle());
        });

        it("should update the titlePosition property", function() {
            header.setTitlePosition(2);
            expect(header.titlePosition).toBe(2);
        });

        it("should not allow a titlePosition greater than the max item index", function() {
            header.setTitlePosition(3);
            expect(header.items.getAt(2)).toBe(header.getTitle());
            expect(header.titlePosition).toBe(2);
        });
    });
});