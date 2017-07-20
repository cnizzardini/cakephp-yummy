var YummySearch = document.getElementById('yummy-search-form');

if( YummySearch.length > 0 ){

    YummySearch.addEventListener('click', function(e){
        e = e || window.event;
        var target = e.target || e.srcElement;
        
        /* remove row */
        if( target.type == 'button' && target.className.match('minus-button') ){
            target.parentElement.parentElement.parentElement.remove();
        }
        
        /* add row */
        if( target.type == 'button' && target.className.match('plus-button') ){
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
}