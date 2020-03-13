define([
    'prototype'
], function () {
    AmastyBannersInjector = Class.create();
    AmastyBannersInjector.prototype = {
        container: null,
        after: null,
        wrapper: null,
        wrappers: $A([]),
        element: null,
        wrapperHtml: '',
        subContainerHtml: null,
        subContainer: null,
        initialized: false,
        afterProductRow: 0,
        initialize: function (containerSelector,
                              itemSelector,
                              afterProductRow,
                              afterProductNum,
                              width) {
            this.initialized = $$(containerSelector).length > 0;

            if (!this.initialized) {
                console.warn(
                    'Failed to initialize banner: Can\'t find an element with selector "' + containerSelector + '"'
                );

                return;
            }

            this.afterProductRow = afterProductRow;
            this.afterProductNum = afterProductNum;

            this.container = $$(containerSelector)[0];
            if (afterProductNum == -1) {
                this.after = $$(itemSelector)[0];
            } else {
                this.after = $$(itemSelector)[afterProductNum];
            }
            this.wrapperHtml = this.after ? this.after.clone().outerHTML : '<li class="item last"></li>';

            var screenWidth = window.innerWidth;
            var number = screenWidth / $$(itemSelector)[0].getWidth();
            if (number > width) {
                this.width = width;
            } else {
                this.width = parseInt(number);
            }
        },
        inject: function (element) {
            if (!this.initialized) {
                return;
            }

            this.element = element;
            this.element.hide();
            this.element.addClassName('ambanners-injected-banner');
            for (var i = 0; i < this.width; i++) {
                var wrapper = new Element('div');
                wrapper.insert(this.wrapperHtml);
                var insert = wrapper.down();
                insert.id = this.guid();
                this.wrappers.push(insert);
            }
            Event.observe(window, 'resize', this.resize.bind(this));

            this.resize();
        },
        guid: function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        },
        insertWrapper: function (insert) {
            insert.addClassName('ambanners-injected');

            if (this.after) {
                if (this.afterProductNum == -1) {
                    this.after.insert({
                        'before': insert
                    });
                } else {
                    this.after.insert({
                        'after': insert
                    });
                }
            } else if (this.container) {
                if (parseInt(this.afterProductRow) > 1) {
                    this.container.insert({
                        'bottom': insert
                    });
                } else {
                    this.container.insert({
                        'top': insert
                    });
                }
            }
        },
        top: function () {
            var top = 0;
            if (this.wrappers.length > 0) {
                var tops = {};
                this.wrappers.each(function (wrapper) {
                    if ($(wrapper.id)) {
                        if (tops[$(wrapper.id).cumulativeOffset().top]) {
                            tops[$(wrapper.id).cumulativeOffset().top]++;
                        } else {
                            tops[$(wrapper.id).cumulativeOffset().top] = 1;
                        }
                    }
                });

                var max = 0;
                $H(tops).each(function (value) {
                    if (value.value > max) {
                        top = value.key;
                        max = value.value;
                    }
                });
            }

            return top;
        },
        resize: function () {
            this.element.hide();
            if (this.wrappers.length > 0) {
                this.wrappers.each(function (wrapper) {
                    if (!$(wrapper.id)) {
                        this.insertWrapper(wrapper);
                    }
                }.bind(this));

                var top = this.top();
                var removeWrappers = [];

                this.wrappers.each(function (wrapper) {
                    if (wrapper.cumulativeOffset().top != top) {
                        removeWrappers.push(wrapper);
                    }
                });

                $A(this.removeWrappers).each(function (wrapper) {
                    wrapper.remove();
                });

                this.showBanner();
            }
        },
        showBanner: function () {
            var width = 0;
            var insertWrapper;
            var left = 1000;
            this.wrappers.each(function (wrapper) {
                if ($(wrapper.id)) {
                    width += wrapper.getWidth();

                    if (wrapper.cumulativeOffset().left < left) {
                        left = wrapper.cumulativeOffset().left;
                        insertWrapper = wrapper;
                    }
                }
            });
            var screenWidth = window.innerWidth;

            if (insertWrapper) {
                insertWrapper.insert(this.element);
                if (width < screenWidth) {
                    this.element.setStyle({
                        'position': 'absolute',
                        'width': width + 'px'
                    });
                } else {
                    this.element.setStyle({
                        'position': 'absolute',
                        'width': 100 + '%'
                    });
                }

                insertWrapper.setStyle({
                    'min-height': this.element.getHeight() + 'px'
                })
            }
            this.element.show();
        }
    };

    return AmastyBannersInjector;
});
