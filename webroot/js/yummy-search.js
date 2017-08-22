if (document.getElementById('yummy-search-form') != null) {

    document.getElementById('yummy-search-form').addEventListener('click', function(e){
        e = e || window.event;
        var target = e.target || e.srcElement;
        
        /* remove row */
        if (target.type == 'button' && target.className.match('minus-button')) {
            target.parentElement.parentElement.parentElement.remove();
        }
        
        /* add row */
        if (target.type == 'button' && target.className.match('plus-button')) {
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
    
    // @todo this needs to fire onload for each yummy element
    
    document.getElementById('yummy-search-form').addEventListener('change', function(e){
        e = e || window.event;
        var target = e.target || e.srcElement;
        if (target.tagName.toLowerCase() =='select' && target.className.match('yummy-field')) {
            var option = target.options[ target.selectedIndex ];
            var dataType = option.getAttribute('data-type').toLowerCase();
            
            var row = target.parentElement.parentElement.parentElement.parentElement;
            
            var operator = row.getElementsByClassName('yummy-operator');
            var input = row.getElementsByClassName('yummy-input');
            
            var items = false;
            if (dataType == 'list') {
                var list = option.getAttribute('data-items');
                items = list.split(',');
            }
            
            var event = new CustomEvent(
                "yummySearchFieldChange", 
                {
                    detail: {
                        field: target,
                        operator: operator[0],
                        input: input[0],
                        dataType: dataType,
                        items: items
                    },
                    bubbles: true,
                    cancelable: true
                }
            );
            e.currentTarget.dispatchEvent(event);
        }
        
    },false);
}