jQuery.fn.extend({
    hqlbeatify: function ( type ) {
        var text 
        if ( type == 'val' ){
            text = jQuery(this).val();
        } else {
            text = jQuery(this).text();
        }
        var text_create = '';
        text_create = text.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ/g, "a").replace(/đ|Đ/g, "d").replace(/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/g,"y").replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ/g,"u").replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ/g,"o").replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ/g, "e").replace(/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ì|Ĩ/g,"i");
    return text_create;
    }
});


jQuery(document).ready(function(){

    jQuery.expr[':'].icontains = function(a, i, m) {
        return jQuery(a).hqlbeatify().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
    };

    var taxonomy = jQuery('#tax_keyword').data('taxonomy');
    
    jQuery('#tax_keyword').keyup(function() {
        var tax_keyword = jQuery('#tax_keyword').hqlbeatify('val');

        list = jQuery("#" + taxonomy + "checklist li, #" + taxonomy + "checklist-pop li");
        list.hide();
        // console.log( jQuery("#" + taxonomy + "checklist label:icontains('" + tax_keyword + "')") );
        var containing_labels = jQuery("#" + taxonomy + "checklist label:icontains('" + tax_keyword + "'), #" + taxonomy + "checklist-pop label:icontains('" + tax_keyword + "')");
        containing_labels.closest("li").find("li").andSelf().show();
        containing_labels.parents("#" + taxonomy + "checklist li, #" + taxonomy + "checklist-pop li").show();
    });

    // Sync checked items between "All {taxonomy}" and "Most used" lists.
    jQuery('#' + taxonomy + 'checklist, #' + taxonomy + 'checklist-pop').on( 'click', 'li.popular-category > label input[type="radio"]', function() {
        var t = jQuery(this), c = t.is(':checked'), id = t.val();

        if ( id && t.parents('#taxonomy-'+taxonomy).length ) {
            jQuery('#' + taxonomy + 'checklist-pop li.popular-category > label input[type="radio"]').removeAttr('checked');
            jQuery('#in-' + taxonomy + '-' + id + ', #in-popular-' + taxonomy + '-' + id).prop( 'checked', c );
        }
    });

    jQuery('.repeater').repeater({
        show: function () {
            jQuery(this).slideDown();
        },
        hide: function (deleteElement) {
            if(confirm('Are you sure you want to delete this element?')) {
                jQuery(this).slideUp(deleteElement);
            }
        }
    });

});