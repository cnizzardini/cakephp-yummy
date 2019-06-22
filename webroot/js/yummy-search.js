/**
 * YummySearch
 */
(function yummySearchModule(factory) {
    "use strict";

    if (typeof define === "function" && define.amd) {
        define(factory);
    }
    else if (typeof module != "undefined" && typeof module.exports != "undefined") {
        module.exports = factory();
    }
    else {
        /* jshint sub:true */
        window.YummySearch = factory();
    }
})(function yummySearchFactory() {
    "use strict";

    if (typeof window === "undefined" || !window.document) {
        return function yummySearchError() {
            throw new Error("yummy-search.js requires a window with a document");
        };
    }

    function YummySearch(){

    }

    /**
     * Creates and dispatches event
     * @param {dom object} row
     * @returns {Boolean}|{Void}
     */
    YummySearch.yummySearchFieldChangeEvent = function (row){

        var field = this.getSearchColumnField(row);
        var option = this.getSearchColumnFieldOptionElement(field);

        if (option === false) {
            console.log('error option value cannot be empty');
            return false;
        }

        var dataType = option.getAttribute('data-type').toLowerCase();
        var operator = row.getElementsByClassName('yummy-operator')[0];
        var prevOperator = this.getPreviousOperator(operator);
        var prevValue = this.getPreviousSearchValue(row)
        var items = this.getCustomSearchList(option);

        var self = this;

        new Promise(function(resolve, reject) {
            if (self.setOperators(option, row)) {
                resolve();
            }
        }).then(function(){
            self.setDefaultOperator(row);
        });

        var detail = {
            field: field,
            operator: operator,
            input: row.getElementsByClassName('yummy-input')[0],
            dataType: dataType,
            items: items,
            prevValue: prevValue,
            prevOperator: prevOperator,
        };


        this.setDropDownList(detail);

        var event = new CustomEvent(
            "yummySearchFieldChange",
            {
                detail: detail,
                bubbles: true,
                cancelable: true
            }
        );

        field.dispatchEvent(event);
    };

    YummySearch.getSearchColumnField = function(row) {
        var fields = row.getElementsByClassName('yummy-field');
        return fields[0];
    };

    YummySearch.getSearchColumnFieldOptionElement = function(field) {
        var option = field.options[ field.selectedIndex ];
        if (option.getAttribute('value') === null || option.getAttribute('value').trim() === '') {
            return false;
        }
        return option;
    };

    YummySearch.getPreviousOperator = function (operator) {
        var prevOperator = false;

        if (operator.tagName.toLowerCase() === 'select') {
            for (var i=0; i<operator.length; i++) {
                if (operator[ i ].getAttribute('selected') !== null) {
                    prevOperator = operator[ i ].getAttribute('value');
                }
            }
        } else {
            prevOperator = operator.getAttribute('value') == 'null' ? 'like' : operator.getAttribute('value');
        }

        return prevOperator;
    };

    YummySearch.getPreviousSearchValue = function(row) {
        var input = row.getElementsByClassName('yummy-input')[0];
        var operator = row.getElementsByClassName('yummy-operator')[0];
        var prevValue = false;
        if (input !== undefined) {
            if (input.tagName.toLowerCase() === 'input') {
                prevValue = input.getAttribute('value');
            } else if (input.tagName.toLowerCase() === 'select') {
                for (var i=0; i<input.length; i++) {
                    if (operator[ i ].getAttribute('selected') !== null) {
                        prevValue = operator[ i ].getAttribute('value');
                    }
                }
            }
        }
        return prevValue;
    };

    YummySearch.setOperators = function(option, row) {

        if (option.getAttribute('data-operators') === null) {
            return true;
        }

        var operators = option.getAttribute('data-operators').split(',').filter(function(op){
            if (op.trim().length > 0) {
                return true;
            }
        });

        var defaultOperators = row.getElementsByClassName('yummy-operator')[0].options;

        if (operators.length > 0) {
            for (var i=0; i < defaultOperators.length; i++) {
                console.log(row.getElementsByClassName('yummy-operator')[0].options[i]);
                if (operators.indexOf(defaultOperators[i].value) === -1) {
                    row.getElementsByClassName('yummy-operator')[0].options[i].setAttribute('hidden', true);
                } else {
                    row.getElementsByClassName('yummy-operator')[0].options[i].removeAttribute('hidden');
                }
            }
        } else {
            for (var i=0; i < defaultOperators.length; i++) {
                row.getElementsByClassName('yummy-operator')[0].options[i].removeAttribute('hidden');
            }
        }

        return true;
    };

    YummySearch.getCustomSearchList = function(option) {
        if (option.getAttribute('data-type').toLowerCase() === 'list') {
            return option.getAttribute('data-items').split(',');
        }
        return false;
    };

    YummySearch.setDefaultOperator = function(row) {

        if (row.getElementsByClassName('yummy-operator')[0].value.length > 0) {
            return true;
        }

        var options = row.getElementsByClassName('yummy-operator')[0].options;

        for (var i=0; i < options.length; i++) {
            var isVisible = options[i].getAttribute('hidden') === false || options[i].getAttribute('hidden') === null;
            var isActive = options[i].getAttribute('disabled') === false || options[i].getAttribute('disabled') === null;
            if (isVisible === true && isActive === true && options[i].value.length > 0) {
                row.getElementsByClassName('yummy-operator')[0].selectedIndex = i;
                break;
            }
        }

        return true;
    };

    YummySearch.setDropDownList = function(detail) {

        var selects = detail.input.parentNode.getElementsByTagName('select');
        for (var i=0; i<selects.length; i++) {
            selects[i].remove();
        }

        if (detail.dataType !== 'list') {
            detail.input.style.display = '';
            detail.input.removeAttribute('disabled');
            return true;
        }

        detail.input.style.display = 'none';
        detail.input.setAttribute('disabled', 'disabled');

        var dropdown = document.createElement("select");
        dropdown.classList = 'form-control border-input yummy-search';
        dropdown.name = 'YummySearch[search][]';

        for (var i=0; i<detail.items.length; i++) {
            var option = document.createElement("option");
            option.value = detail.items[ i ];
            option.text = detail.items[ i ];
            if (detail.prevValue === detail.items[ i ]) {
                option.selected = 'selected';
            }
            dropdown.appendChild(option);
        }

        detail.input.parentNode.appendChild(dropdown);
        return true;
    };

    /**
     * Change event for when search field (column) is changed
     * @param {type} e
     * @param {type} target
     * @returns {undefined}
     */
    YummySearch.changeEvent = function(e,target){
        function collectionHas(a, b) { //helper function (see below)
            for(var i = 0, len = a.length; i < len; i ++) {
                if(a[i] == b) return true;
            }
            return false;
        }
        function findParentBySelector(elm, selector) {
            var all = document.querySelectorAll(selector);
            var cur = elm.parentNode;
            while(cur && !collectionHas(all, cur)) { //keep going up until you find a match
                cur = cur.parentNode; //go up
            }
            return cur; //will return null if not found
        }
        var row = findParentBySelector(target, '.yummy-search-row');
        YummySearch.yummySearchFieldChangeEvent(row);
    };

    /**
     * To be called on load
     * @returns {undefined}
     */
    YummySearch.load = function(){

        if (document.getElementById('yummy-search-form') !== null) {
            var rows = document.getElementsByClassName('yummy-search-row');

            for (var i=0; i<rows.length; i++) {
                YummySearch.yummySearchFieldChangeEvent(rows[ i ]);
            }

            YummySearch.bindEventListeners();

        } else {
            console.log('yummy-search-form not found')
        }
    };

    /**
     * Binds event listeners
     * @returns {undefined}
     */
    YummySearch.bindEventListeners = function(){

        // change event
        document.getElementById('yummy-search-form').addEventListener('change', function(e){
            e = e || window.event;
            var target = e.target || e.srcElement;
            if (target.tagName.toLowerCase() ==='select' && target.className.match('yummy-field')) {
                YummySearch.changeEvent(e,target);
            }

        },false);

        // create row
        document.getElementById('yummy-search-form').addEventListener('click', function(e){
            e = e || window.event;
            var target = e.target || e.srcElement;

            /* remove row */
            if (target.type === 'button' && target.className.match('minus-button')) {
                target.parentElement.parentElement.parentElement.remove();
            }

            /* add row */
            if (target.type === 'button' && target.className.match('plus-button')) {

                var rows = document.getElementsByClassName("yummy-search-row");
                var createRow = rows[0].cloneNode(true);
                var yummyAttributes = document.getElementById('yummy-attributes');
                // column 1
                var searchBy = createRow.getElementsByTagName('select')[0];
                // column 2
                var operator = createRow.getElementsByTagName('select')[1];
                // column 3 either dropdown or input
                var dropdown = createRow.getElementsByTagName('select')[2];
                var textInput = createRow.getElementsByTagName('input')[0];
                // plus / minus buttons
                var plusBtn = createRow.getElementsByClassName('plus-button')[0];
                var minusBtn = createRow.getElementsByClassName('minus-button')[0];

                // reset search-by drop down
                searchBy.options[0].defaultSelected = true;
                // reset condition dropdown
                operator.options[0].defaultSelected = true;

                // hide dropdown
                if (dropdown !== null && dropdown !== undefined) {
                    dropdown.remove();
                }

                // reset textInput
                textInput.removeAttribute('disabled');
                textInput.value = '';

                // make textInput visible
                if (yummyAttributes !== null) {
                    textInput.classList.remove(yummyAttributes.getAttribute('hide'));
                } else {
                    textInput.setAttribute('style','display:block');
                }

                // remove plusBtn
                plusBtn.remove();

                // show minusBtn
                if (yummyAttributes !== null) {
                    minusBtn.classList.remove(yummyAttributes.getAttribute('hide'));
                } else {
                    minusBtn.setAttribute('style','display:inline');
                }

                // add the row
                rows[ rows.length - 1].after(createRow);
                YummySearch.yummySearchFieldChangeEvent(createRow);
            }

        },false);
    };
    return YummySearch;
});