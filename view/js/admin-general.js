function mpObject(){
    
    /*
     * Hold temporary content for new Tab
     * 
     * @var string HTML content for new Tab
     * @access private
     */
    this.tempContent = '';
}

mpObject.prototype.addNewSoS = function(type){
    var params = {
        mp_nonce : mp_userSettings.nonce,
        action : 'mp_ajax',
        subact : 'getHTML_sos',
        type : type
    }
    jQuery.post(ajaxurl, params, function(data){
        if (mpObj.success(data)){
            jQuery('#object_args-4 table tbody').append(data.html);
        }
    }, 'json');
}

mpObject.prototype.addNewTab = function(type){

    var params = {
        mp_nonce : mp_userSettings.nonce,
        action : 'mp_ajax',
        subact : 'getHTML_tab',
        type   : type
    }
    jQuery.post(ajaxurl, params, function(data){
        if (mpObj.success(data)){
            jQuery('div[data="' + type + '"] > ul').show();
            jQuery('div[data="' + type + '"] .notice-message').hide();
            jQuery('div[data="' + type + '"]').tabs('option', 'tabTemplate', data.liHTML);
            mpObj.tempContent = data.divHTML;
            jQuery('div[data="' + type + '"]').tabs('add', '#' + type + '-' + data.id, data.title);

            jQuery('#object_args-4 table tbody').sortable('destroy');
            jQuery('#object_args-4 table tbody').sortable();
        }
    }, 'json');
}

mpObject.prototype.deleteRow = function(selector, force){
    if (!force){ //show confirmation message
        //TODO : MINOR - Render confirmation message
    }
    jQuery(selector).remove();
}

mpObject.prototype.success = function(data){
    if (data.status == 'success'){
        var result = true;
    }else{
        //TODO : MAJOR - render error dialog
        result = false;
    }
    
    return result;
}

mpObject.prototype.updateText = function($) {
    var attemptedDate, originalDate, currentDate, publishOn, postStatus = $('#post_status'),
    optPublish = $('option[value="publish"]', postStatus), aa = $('#aa').val(),
    mm = $('#mm').val(), jj = $('#jj').val(), hh = $('#hh').val(), mn = $('#mn').val(),
    stamp = $('#timestamp').html();
    
    attemptedDate = new Date( aa, mm - 1, jj, hh, mn );
    originalDate = new Date( $('#hidden_aa').val(), $('#hidden_mm').val() -1, $('#hidden_jj').val(), $('#hidden_hh').val(), $('#hidden_mn').val() );
    currentDate = new Date( $('#cur_aa').val(), $('#cur_mm').val() -1, $('#cur_jj').val(), $('#cur_hh').val(), $('#cur_mn').val() );

    if ( attemptedDate.getFullYear() != aa || (1 + attemptedDate.getMonth()) != mm || attemptedDate.getDate() != jj || attemptedDate.getMinutes() != mn ) {
        $('.timestamp-wrap', '#timestampdiv').addClass('form-invalid');
        return false;
    } else {
        $('.timestamp-wrap', '#timestampdiv').removeClass('form-invalid');
    }

    if ( attemptedDate > currentDate && $('#original_post_status').val() != 'future' ) {
        publishOn = postL10n.publishOnFuture;
    //$('#publish').val( postL10n.schedule );
    } else if ( attemptedDate <= currentDate && $('#original_post_status').val() != 'publish' ) {
        publishOn = postL10n.publishOn;
    // $('#publish').val( postL10n.publish );
    } else {
        publishOn = postL10n.publishOnPast;
    // $('#publish').val( postL10n.update );
    }
    if ( originalDate.toUTCString() == attemptedDate.toUTCString() ) { //hack
        $('#timestamp').html(stamp);
    } else {
        $('#timestamp').html(
            publishOn + ' <b>' +
            $('option[value="' + $('#mm').val() + '"]', '#mm').text() + ' ' +
            jj + ', ' +
            aa + ' @ ' +
            hh + ':' +
            mn + '</b> '
            );
    }

    if ( $('input:radio:checked', '#post-visibility-select').val() == 'private' ) {
        // $('#publish').val( postL10n.update );
        if ( optPublish.length == 0 ) {
            postStatus.append('<option value="publish">' + postL10n.privatelyPublished + '</option>');
        } else {
            optPublish.html( postL10n.privatelyPublished );
        }
        $('option[value="publish"]', postStatus).prop('selected', true);
        $('.edit-post-status', '#misc-publishing-actions').hide();
    } else {
        if ( $('#original_post_status').val() == 'future' || $('#original_post_status').val() == 'draft' ) {
            if ( optPublish.length ) {
                optPublish.remove();
                postStatus.val($('#hidden_post_status').val());
            }
        } else {
            optPublish.html( postL10n.published );
        }
        if ( postStatus.is(':hidden') )
            $('.edit-post-status', '#misc-publishing-actions').show();
    }
    $('#post-status-display').html($('option:selected', postStatus).text());
    if ( $('option:selected', postStatus).val() == 'private' || $('option:selected', postStatus).val() == 'publish' ) {
        $('#save-post').hide();
    } else {
        $('#save-post').show();
        if ( $('option:selected', postStatus).val() == 'pending' ) {
            $('#save-post').show().val( postL10n.savePending );
        } else {
            $('#save-post').show().val( postL10n.saveDraft );
        }
    }
    return true;
}


jQuery(document).ready(function(){
    //initiate object
    mpObj = new mpObject();
    
    jQuery('.mp-tabs').tabs({
        add: function( event, ui ) {
            jQuery( ui.panel ).append( mpObj.tempContent );
        }
    });
    // close icon: removing the tab on click
    jQuery( ".mp-tabs span.ui-icon-close" ).live( "click", function() {
        var tabs = jQuery(this).closest('.mp-tabs');
        var index = jQuery( "li", tabs ).index( jQuery( this ).parent() );
        tabs.tabs( "remove", index );
    });
    //  jQuery('.mp-tabs [title]').tooltip();
    jQuery('.mp_button > a').button({
        icons: {
            primary: "ui-icon-plusthick"
        }
    });
    //Show on Screen sortable list
    jQuery('#object_args-4 table tbody').sortable();
});