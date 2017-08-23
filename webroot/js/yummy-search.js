window.onload = function(){
    if (document.getElementById('yummy-search-form') != null) {

        var YummySearch = YummySearch || {};

        /**
         * Change event for when search field (column) is changed
         * @param {type} e
         * @param {type} target
         * @returns {undefined}
         */
        YummySearch.changeEvent = function(e,target){

            var option = target.options[ target.selectedIndex ];
            var dataType = option.getAttribute('data-type').toLowerCase();

            var row = target.parentElement.parentElement.parentElement.parentElement;

            var operator = row.getElementsByClassName('yummy-operator')[0];
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
                        field: target,
                        operator: operator,
                        input: input,
                        dataType: dataType,
                        items: items,
                        prevValue: input.getAttribute('value'),
                        prevOperator: operator.getAttribute('value')
                    },
                    bubbles: true,
                    cancelable: true
                }
            );
            e.currentTarget.dispatchEvent(event);
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

                    createRow.getElementsByTagName('select')[0].options[0].defaultSelected = true;
                    createRow.getElementsByTagName('select')[1].options[0].defaultSelected = true;
                    createRow.getElementsByTagName('input')[0].value = '';
                    createRow.getElementsByClassName('plus-button')[0].remove();
                    createRow.getElementsByClassName('minus-button')[0].setAttribute('style','display:inline');

                    rows[ rows.length - 1].after(createRow);
                }

            },false);
        };

        /**
         * To be called on load
         * @returns {undefined}
         */
        YummySearch.onLoad = function(){
            var rows = document.getElementsByClassName('yummy-search-row');

            for (var i=0; i<rows.length; i++) {
                var row = rows[ i ];
                var field = row.getElementsByClassName('yummy-field')[0];
                
                var option = field.options[ field.selectedIndex ];
                var dataType = option.getAttribute('data-type').toLowerCase();

                var operator = row.getElementsByClassName('yummy-operator')[0];
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
                            prevOperator: operator.getAttribute('value')
                        },
                        bubbles: true,
                        cancelable: true
                    }
                );
            }
        };

        YummySearch.bindEventListeners();
        YummySearch.onLoad();
    }
};