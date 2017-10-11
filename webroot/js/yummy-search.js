
if (document.getElementById('yummy-search-form') !== null) {

    var YummySearch = YummySearch || {};

    /**
     * Creates and dispatches event
     * @param {dom object} row
     * @returns {Boolean}|{Void}
     */
    YummySearch.yummySearchFieldChangeEvent = function (row){

        var fields = row.getElementsByClassName('yummy-field');
        var field = fields[0];

        var option = field.options[ field.selectedIndex ];
        if (option.getAttribute('value') === null || option.getAttribute('value').trim() === '') {
            return false;
        }

        var dataType = option.getAttribute('data-type').toLowerCase();

        var operator = row.getElementsByClassName('yummy-operator')[0];
        var prevOperator = operator.getAttribute('value');
        
        if (operator.tagName.toLowerCase() === 'select') {
            for (var i=0; i<operator.length; i++) {
                if (operator[ i ].getAttribute('selected') !== null) {
                    prevOperator = operator[ i ].getAttribute('value');
                }
            }
        }
        
        var input = row.getElementsByClassName('yummy-input')[0];
        var items = false;
        if (dataType === 'list') {
            var list = option.getAttribute('data-items');
            items = list.split(',');
        }

        var event = new CustomEvent(
            "yummySearchFieldChange", 
            {
                detail: {
                    field: field,
                    operator: operator,
                    input: input,
                    dataType: dataType,
                    items: items,
                    prevValue: input.getAttribute('value'),
                    prevOperator: prevOperator
                },
                bubbles: true,
                cancelable: true
            }
        );

        field.dispatchEvent(event);
    };

    /**
     * Change event for when search field (column) is changed
     * @param {type} e
     * @param {type} target
     * @returns {undefined}
     */
    YummySearch.changeEvent = function(e,target){
        var row = target.parentElement.parentElement.parentElement.parentElement;
        //console.log(row);
        YummySearch.yummySearchFieldChangeEvent(row);
    };

    /**
     * To be called on load
     * @returns {undefined}
     */
    YummySearch.load = function(){
        var rows = document.getElementsByClassName('yummy-search-row');

        for (var i=0; i<rows.length; i++) {
            YummySearch.yummySearchFieldChangeEvent(rows[ i ]);
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
                    textInput.className += yummyAttributes.getAttribute('show');
                } else {
                    textInput.setAttribute('style','display:block');
                }
                
                // remove plusBtn
                plusBtn.remove();
                
                // show minusBtn
                if (yummyAttributes !== null) {
                    minusBtn.className += yummyAttributes.getAttribute('show');
                } else {
                    minusBtn.setAttribute('style','display:inline');
                }
                
                // add the row
                rows[ rows.length - 1].after(createRow);
            }

        },false);
    };

    YummySearch.bindEventListeners();
}
