define([
    'jquery',
    'ko',
    'underscore',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    'Magento_Catalog/js/catalog-add-to-cart'
], function ($, ko, _, $t, priceUtils) {

    var Autocomplete = function (input) {
        this.$input = $(input);
        this.isVisible = false;
        this.isShowAll = true;
        this.loading = false;
        this.config = [];
        this.result = false
    };

    Autocomplete.prototype = {
        placeholderSelector:      '.mst-searchautocomplete__autocomplete',
        wrapperSelector:          '.mst-searchautocomplete__wrapper',
        additionalColumnSelector: 'mst-2-cols',
        model:                    null,

        init: function (config) {
            this.config = _.defaults(config, this.defaults);
            window.priceFormat = this.config.priceFormat;

            this.doSearch = _.debounce(this._doSearch, this.config.delay);

            this.$input.after($('#searchAutocompletePlaceholder').html());

            this.xhr = null;

            this.$input.on("keyup", function (event) {
                this.clickHandler(event)
            }.bind(this));

            this.$input.on("click focus", function () {
                this.clickHandler()
            }.bind(this));

            this.$input.on("input", function () {
                this.inputHandler()
            }.bind(this));

            $(document).on("mousedown click", function (event) {
                this.clickHandler(event);
            }.bind(this));

            ko.bindingHandlers.highlight = {
                init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    let arQuery = bindingContext.$parents[2].result().query.split(' ');
                    let arSpecialChars = [
                        {'key': 'a', 'value': '(à|â|ą|a)'},
                        {'key': 'c', 'value': '(ç|č|c)'},
                        {'key': 'e', 'value': '(è|é|ė|ê|ë|ę|e)'},
                        {'key': 'i', 'value': '(î|ï|į|i)'},
                        {'key': 'o', 'value': '(ô|o)'},
                        {'key': 's', 'value': '(š|s)'},
                        {'key': 'u', 'value': '(ù|ü|û|ū|ų|u)'}
                    ];
                    let html = $(element).text();

                    arQuery.forEach(function (word, key) {
                        if ($.trim(word)) {
                            word = word.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
                            arSpecialChars.forEach(function (match, idx) {
                                word = word.replace(new RegExp(match.key, 'g'), match.value);
                            });

                            if ("span".indexOf(word.toLowerCase()) === -1) {
                                html = html.replace(new RegExp('(' + word + '(?![^<>]*>))', 'ig'), function ($1, match) {
                                    return '<span class="mst-searchautocomplete__highlight">' + match + '</span>';
                                });
                            }
                        }
                    });
                    $(element).html(html);
                }
            };
        },

        clickHandler: function (event) {
            if (!event) {
                if (this.result) {
                    this.setActiveState(true);
                    this.ensurePosition();
                    setInterval(function () {
                        this.ensurePosition();
                    }.bind(this), 10);
                } else {
                    this.result = this.search();
                }
            } else {
                if (event.keyCode === 13) {
                    $(event.target).closest('form').submit();
                }

                if ($(event.target)[0] != this.$input[0] && !$(event.target).closest(this.$placeholder()).length) {
                    this.setActiveState(false);
                }

                if ($(event.target).hasClass('mst-searchautocomplete__close')) {
                    this.setActiveState(false);
                }
            }

        },

        setActiveState: function (isActive) {
            $('body').toggleClass('searchautocomplete__active', isActive);
            this.$input.toggleClass('searchautocomplete__active', isActive);
            this.$placeholder().toggleClass('_active', isActive);

            //magento minisearch
            $(this.$input[0].form).toggleClass('active', isActive);
            $(this.$input[0].labels).each(function () {
                $(this).toggleClass('active', isActive);
            })
        },

        inputHandler: function () {
            $('body').addClass('searchautocomplete__active');

            this.result = this.search();

            setTimeout(function () {
                if (this.result) {
                    this.$placeholder().addClass('_active');
                    this.ensurePosition();
                } else {
                    this.$placeholder().removeClass('_active');
                }
            }.bind(this), 200);

            this.ensurePosition();
        },

        $spinner: function () {
            return this.$placeholder().find(".mst-searchautocomplete__spinner");
        },

        search: function () {
            if ($(this.$input).val().length > 0) {
                $('.actions .action.search').prop('disabled', false);
            }

            this.ensurePosition();

            this.$input.off("keydown");
            this.$input.off("blur");

            if (this.xhr != null) {
                this.xhr.abort();
                this.xhr = null;
            }

            if (this.$input.val().length >= this.config.minSearchLength) {
                this.doSearch(this.$input.val());
            } else {
                this.$placeholder().removeClass(this.additionalColumnSelector);
                return this.doPopular();
            }

            return true;
        },

        _doSearch: function (query) {
            //this.$wrapper().remove();
            this.isVisible = true;

            this.$spinner().show();

            this.xhr = $.ajax({
                url:      this.config.url,
                dataType: 'json',
                type:     'GET',
                data:     {
                    q:        query,
                    store_id: this.config.storeId,
                    cat:      false
                },
                success:  function (data) {
                    this.processApplyBinding(data);

                    this.$spinner().hide();
                }.bind(this)
            });
        },

        viewModel: function (data) {
            if (this.model === null) {
                this.model = {
                    result:  ko.observable({}),
                    loading: ko.observable(false),

                    onMouseOver: function (item, event) {
                        $(event.currentTarget).addClass('_active');
                    }.bind(this),

                    onMouseOut: function (item, event) {
                        $(event.currentTarget).removeClass('_active');
                    }.bind(this),

                    afterRender: function (el) {
                        $(el).catalogAddToCart({});
                    }.bind(this),

                    onClick: function (item, event) {
                        if (event.button === 0) { // left click
                            event.preventDefault();

                            if ($(event.target).closest('.tocart').length) {
                                return;
                            }

                            if (event.target.nodeName === 'A'
                                || event.target.nodeName === 'IMG'
                                || event.target.nodeName === 'LI'
                                || event.target.nodeName === 'SPAN'
                                || event.target.nodeName === 'DIV') {

                                this.enter(item);
                            }
                        }
                    }.bind(this),

                    onSubmit: function (item, event) {
                    }.bind(this),

                    bindPrice: function (item, event) {
                        return true;
                    }.bind(this)
                };
            }

            this.model.loading(this.loading);
            this.model.result(data);
            this.model.result().isShowAll = this.isShowAll;
            this.model.form_key = document.cookie.match('(^|;) ?form_key=([^;]*)(;|$)');;

            return this.model;
        },

        enter: function (item) {
            if (item.url) {
                window.location.href = item.url;
            } else {
                this.pasteToSearchString(item.query);
            }
        },

        pasteToSearchString: function (searchTerm) {
            this.$input.val(searchTerm);
            this.search();
        },

        doPopular: function () {
            this.$spinner().hide();
            if (this.config.popularSearches.length) {
                this.processApplyBinding(this._showQueries(this.config.popularSearches));

                return true;
            }

            return false;
        },

        processApplyBinding: function (data) {
            if (this.model === null) {
                if (this.$wrapper().length > 0) {
                    if (!!ko.dataFor(this.$wrapper())) {
                        ko.cleanNode(this.$wrapper());
                    }
                }

                this.$wrapper().remove();
                const wrapper = $('#searchAutocompleteWrapper').html();

                this.$placeholder().append(wrapper);

                this.viewModel(data);

                ko.applyBindings(this.model, this.$wrapper()[0]);
            }

            this.viewModel(data)

            if (this.config.layout === '2columns' && Object.keys(data.indexes).length > 1) {
                const result = {};
                data.indexes.forEach(function (index) {
                    if (index.items.length > 0) {
                        result[index.identifier] = index.items.length;
                    }
                });

                if (Object.keys(result).length > 1 && typeof result.magento_catalog_product != 'undefined') {
                    this.$placeholder().addClass(this.additionalColumnSelector);
                } else {
                    this.$placeholder().removeClass(this.additionalColumnSelector);
                }
            }

            this.ensurePosition();
        },

        $placeholder: function () {
            return $(this.$input.next(this.placeholderSelector));
        },

        $wrapper: function () {
            return $(this.$input.next(this.placeholderSelector).find(this.wrapperSelector));
        },

        _showQueries: function (data) {
            let self = this;
            let queries = data;
            let items = [];
            let item;
            let result, index;

            _.each(queries, function (query, idx) {
                item = {};
                item.query = query;
                item.enter = function () {
                    self.query = query;
                };

                items.push(item);
            }, this);

            result = {
                totalItems: items.length,
                noResults:  items.length === 0,
                query:      this.$input.val(),
                indexes:    []
            };

            index = {
                totalItems:   items.length,
                isShowTotals: false,
                items:        items,
                identifier:   'popular',
                title:        this.config.popularTitle
            };

            result.indexes.push(index);

            return result;
        },

        ensurePosition: function () {
            var position = this.$input.position();
            var width = this.$placeholder().outerWidth();
            var left = position.left + parseInt(this.$input.css('marginLeft'), 10) + this.$input.outerWidth() - width;
            var top = position.top + parseInt(this.$input.css('marginTop'), 10);

            this.$placeholder()
                .css('top', this.$input.outerHeight() - 1 + top)
                .css('left', left)
                .css('width', this.$input.outerWidth());
        }
    };

    return Autocomplete;
});
