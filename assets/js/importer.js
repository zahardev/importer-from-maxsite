(function($){
    var MCI = {
        init: function(){
            var $maxsiteContentImporter = $('#maxsite-content-importer');

            $maxsiteContentImporter.find('#submit').click(function(e){
                e.preventDefault();
                $maxsiteContentImporter.find('.loader').show();
                $.ajax({
                    method: "GET",
                    url: ajaxurl,
                    data: {
                        action: "import_maxsite_content",
                        maxsite_url: $maxsiteContentImporter.find('input[name=maxsite_url]').val()
                    }
                });
            });
        }
    };

    $(document).ready(function(){
        MCI.init();
    });
})(jQuery);
