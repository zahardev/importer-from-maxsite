(function($){
    var MCI = {
        init: function(){
            var self = this;
            self.$maxsiteContentImporter = $('#maxsite-content-importer');
            self.$maxsiteUrl = self.$maxsiteContentImporter.find('input[name=maxsite_url]');
            self.$loader = self.$maxsiteContentImporter.find('.loader');
            self.$results = self.$maxsiteContentImporter.find('.results');
            self.validateListener();

            self.$maxsiteContentImporter.find('#submit').click(function(e){
                e.preventDefault();
                var isValid = self.validate();
                if(isValid){
                    $(this).attr('disabled', 'disabled');
                    self.$loader.show();
                    self.$results.show();
                    $.ajax({
                        method: "GET",
                        url: ajaxurl,
                        data: {
                            action: "import_maxsite_content",
                            maxsite_url: self.$maxsiteUrl.val()
                        },
                        success: function(res){
                            var resultContainer = $('<div></div>');
                            if (false === res.success) {
                                resultContainer.addClass('error');
                            }
                            resultContainer.html(res.data);
                            self.$results.html(resultContainer);
                            self.$loader.hide();
                        },
                        error: function(){
                            self.$loader.hide();
                            self.$results.hide();
                            alert('Error!');
                        }
                    });
                }
            });
        },
        validate: function(){
            var isValid =  /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(this.$maxsiteUrl.val());
            if(!isValid){
                this.$maxsiteUrl.addClass('error');
            }
            return isValid;
        },
        validateListener: function(){
            var self = this;
            this.$maxsiteUrl.keyup(function(){
                self.$maxsiteUrl.removeClass('error');
            });
        }
    };

    $(document).ready(function(){
        MCI.init();
    });
})(jQuery);
