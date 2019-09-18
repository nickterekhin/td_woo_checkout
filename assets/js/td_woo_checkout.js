(function($){

    $.fn.td_woo_checkout_manager = function(options){
        let _checkout = $.data(this[0],"checkout_obj");
        if(!_checkout)
        {
            _checkout = new $td_woo_checkout_manager(options,this[0]);
            $.data(this[0],"crisis_obj",_checkout);
        }

        return _checkout;
    };
    let $td_woo_checkout_manager = function(options, el)
    {
        this.settings = $.extend(true,{},$td_woo_checkout_manager.defaults,options);
        this.$main_el = $(el);
        this.$yes_btn = this.$main_el.find('button[data-type=yes]');
        this.$no_btn = this.$main_el.find('button[data-type=no]');
        this.btn_hidden_elements = this.$no_btn.data('hiddenElements');
        this.has_account_no = false;
        this.init();
    };

    $.extend($td_woo_checkout_manager, {
        defaults: {
            redirect_url:''
        },
        prototype: {
            init:function(){

                let _self  = this;

                this.$yes_btn.on('click',function(){
                    window.open('http://staging.localviking.com/dfy/registrations/check?redirect_uri='+_self.settings.redirect_url,'_self');
                });
                this.$no_btn.on('click',function(){
                    let hidden_elements_array = _self.btn_hidden_elements!=='undefined'?_self.btn_hidden_elements.split(','):[];

                    _self.has_account_no = true;
                    $.each(hidden_elements_array,function(index,el){
                        $('.'+el).removeClass('td-hide-cart-item');

                    });
                    _self.$main_el.addClass("td-hide-cart-item");
                    _self.$main_el.removeClass("td-show-cart-item");
                    $.ajax({
                        type:'POST',
                        dataType: 'json',
                        url:'/wplab_4/wp-admin/admin-ajax.php',
                        data: {
                            action: 'set_lv_choose',
                            choose:'no'
                        },
                        success:function(data){
                        }
                    });

                });


            }
        }
    });



})(jQuery);