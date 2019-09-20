(function($){

    $.fn.td_woo_checkout_manager = function(options){
        if(!this[0]) return null;
        let _checkout = $.data(this[0],"checkout_obj");
        if(!_checkout)
        {
            _checkout = new $td_woo_checkout_manager(options,this[0]);
            $.data(this[0],"checkout_obj",_checkout);
        }

        return _checkout;
    };
    let $td_woo_checkout_manager = function(options, el)
    {
        this.settings = $.extend(true,{},$td_woo_checkout_manager.defaults,options);
        this.$main_el = $(el);
        this.show_google_auth = false;
        this.init();
    };

    $.extend($td_woo_checkout_manager, {
        defaults: {
            redirect_url:'',
            has_lv_account:false,
            is_lv_subscriber:false
        },
        prototype: {
            init:function(){

                let _self  = this;


               let $dlg = $td_woo_checkout_manager.show_dlg(_self);
                if($dlg)
                {
                    this.$main_el.append($dlg).find("button").on('click',function(){
                        let btn_type = $(this).data('btnType'),
                            dlg_type = $(this).data('dlgType'),
                            hidden_elements = $(this).data('hiddenElements');


                        if(btn_type==='yes')
                        {
                            console.log('Yes '+dlg_type);
                            if(dlg_type==='lv_dlg')
                            {
                                //confirm lv account
                                window.open('http://staging.localviking.com/dfy/registrations/check?redirect_uri='+_self.settings.redirect_url,'_self');
                            }else{
                                //re-validate subscription
                                window.open('http://staging.localviking.com/dfy/registrations/new?redirect_uri='+_self.settings.redirect_url+'&email=demo@localviking.com','_self');
                            }

                        }else
                        {
                            if(dlg_type==='lv_dlg')
                            {
                                //show google auth dialog
                                _self.$main_el.empty().append($td_woo_checkout_manager.dlg_html('google_auth')).find("button").on('click',function(){
                                    window.open('http://staging.localviking.com/dfy/registrations/new?redirect_uri='+_self.settings.redirect_url+'','_self');
                                });
                            }else{
                                let hidden_elements_array =hidden_elements !== 'undefined' ? hidden_elements.split(',') : [];
                                $.each(hidden_elements_array, function (index, el) {
                                    $('.' + el).removeClass('td-hide-cart-item');

                                });
                                _self.$main_el.addClass("td-hide-cart-item");
                                _self.$main_el.removeClass("td-show-cart-item");
                                $.ajax({
                                    type: 'POST',
                                    dataType: 'json',
                                    url: '/wplab_4/wp-admin/admin-ajax.php',
                                    data: {
                                        action: 'set_lv_choose',
                                        choose: 'no'
                                    },
                                    success: function (data) {
                                    }
                                });
                            }
                        }
                    });
                }


            }
        },
        show_dlg:function(_self)
        {
            console.log(_self);
            if(!_self.settings.has_lv_account) {
                if (!_self.show_google_auth)
                    return $td_woo_checkout_manager.dlg_html('lv_dlg');
            }
            if(!_self.settings.is_lv_subscriber)
            {
               return $td_woo_checkout_manager.dlg_html('lv_sub_validate');
            }
            return null;
        },
        dlg_html:function(type)
        {
            let html_out = '',
                hidden_elements = '';

            switch(type)
            {
                case 'lv_dlg':
                    html_out +='<h3>Do you have LocalViking account?<p>please answer the question below</p></h3>';
                    break;
                case 'lv_sub_validate':
                    html_out +='<h3>Are you a paying LocalViking subscriber?<p>please answer the question below</p></h3>';
                    hidden_elements = 'data-hidden-elements="cart_totals,coupon"';
                    break;
                case 'google_auth':
                    html_out +='<h3>Authorize with Google Account</h3>';
                    break;
            }
            html_out += '<div class="td-btn-group">';
            if(type!=='google_auth') {

                html_out += '<button class="td-btn" data-btn-type="yes" data-dlg-type="' + type + '">Yes</button>';
                html_out += '<button class="td-btn" data-btn-type="no" '+hidden_elements+' data-dlg-type="' + type + '">No</button>';

            }
            else{
                html_out+='<button class="td-btn" data-btn-type="yes" data-dlg-type="' + type + '">Authorize</button>'
            }
            html_out += '</div>';

            return html_out;

        }
    });



})(jQuery);