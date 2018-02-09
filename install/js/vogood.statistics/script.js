$(function() {
    $('body').on('click', 'a.store_property-link', function(e){
        e.preventDefault();
        var self = $(this);
        var link = self.attr('href');
        var storeId = self.data("store-id");
        

        $.ajax({
            url: '/bitrix/tools/vogood.statistics/sitevisitscount.php',
            type : 'POST',
            async: false,
            dataType:'json',
            data : {'storeId': storeId},
            success: function (result) {
                if(result !== true){
                    console.log(result);
                }
                window.open(link, '_blank');
            }
        });
    });
    $('body').on('click', '.stat-elem .elem-title', function(){
        $(this).toggleClass('_active');
        $(this).closest('.stat-elem').find('.content-list').slideToggle('slow');
    });
});

