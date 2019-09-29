function ShowText(caption, text){
	$("#show_text_modal_label").html(caption);
	$("#show_text_modal_body").html(text);
	$("#show_text").modal('show');
}

function isError(selector, length, condition){
	$(selector).parent().removeClass("has-error has-success");
	var Value = $(selector).val();
	var Error = false;
	switch(condition){
		case '<':
			Error = (Value.length < length);
			break;
		case '<=':
			Error = (Value.length <= length);
			break;
		case '>':
			Error = (Value.length > length);
			break;
		case '>=':
			Error = (Value.length >= length);
			break;
		case '==':
			Error = (Value.length == length);
			break;
		case '!=':
			Error = (Value.length != length);
			break;
	}
	if (Error)
		$(selector).parent().addClass("has-error");
	else
		$(selector).parent().addClass("has-success");
	return Error;
}
    
function checkLayout(code, text) {
	var charmaps = [{
		code: 'en',
		chars: '`1234567890-='
			+ 'qwertyuiop[]'
			+ 'asdfghjkl;\'\\'
			+ 'zxcvbnm,./'
			+ '~!@#$%^&*()_+'
			+ 'QWERTYUIOP{}'
			+ 'ASDFGHJKL:"|'
			+ 'ZXCVBNM<>? '
	}, {
		code: 'ru',
		chars: 'ё1234567890-='
			+ 'йцукенгшщзхъ'
			+ 'фывапролджэ\\'
			+ 'ячсмитьбю.'
			+ 'Ё!"№;%:?*()_+'
			+ 'ЙЦУКЕНГШЩЗХЪ'
			+ 'ФЫВАПРОЛДЖЭ/'
			+ 'ЯЧСМИТЬБЮ, '
	}];
	
        var charmap;
        
        for (var i = 0; i < charmaps.length; i++) {
            if (charmaps[i].code === code) {
                charmap = charmaps[i];
                break;
            }
        }
        if(charmap === null)
            return true;
        for (i = 0; i < text.length; i++) {
            if (charmap.chars.indexOf(text[i]) == -1) {
                return false;
            }
        }
        return true;
}

function auto_layout_keyboard(str){
        replacer = {
            "q":"й", "w":"ц", "e":"у", "r":"к", "t":"е", "y":"н", "u":"г", 
            "i":"ш", "o":"щ", "p":"з", "[":"х", "]":"ъ", "a":"ф", "s":"ы", 
            "d":"в", "f":"а", "g":"п", "h":"р", "j":"о", "k":"л", "l":"д", 
            ";":"ж", "'":"э", "z":"я", "x":"ч", "c":"с", "v":"м", "b":"и", 
            "n":"т", "m":"ь", ",":"б", ".":"ю", "/":"."
        };
        return str.replace(/[A-z/,.;\'\]\[]/g, function ( x ){
            return x == x.toLowerCase() ? replacer[ x ] : replacer[ x.toLowerCase() ].toUpperCase();
        });
}

if (!$.browser.mobile) {	
	var request = $.ajax({
		type: "GET",
		url: "./js/tips.js",
		dataType: "script",
		cache: true
	});
	request.done(function(msg) {});
	request.fail(function(jqXHR, textStatus) {});
}