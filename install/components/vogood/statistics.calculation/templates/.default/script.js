
$(function() {
    
    $('body').on('submit', '.stat-period', function(e){
        e.preventDefault();

        var form = $(this).serializeArray();
        var empty = true;
        for(var i=0; i<form.length; i++){
            if(empty === false){
                console.log(1);
                break;
            }
            if(form[i].value !== ''){
                console.log(2);
                empty = false;
            }
        }
        if(!empty){
            var sendData = {
                ajax: 'y',
                period: form
            };
            $.ajax({
                type : 'POST',
                data : sendData,
                success: function (data) {
                    try {
                        $('.personal_wrapper').html(data);
                    } catch (e) {
                        console.log(e);
                        console.log(e.message);
                    }

                }
            });
        }else{
            $('.error-message').removeClass('_hide').addClass('_visible');

        }
        
    })
});


