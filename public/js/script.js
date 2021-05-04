jQuery(document).ready(function ($){
    $('.details a').on('click' , function (e){
        e.preventDefault();
        let match = $(this).parents('.details').prev();
        let collapse = $(this).attr('aria-controls');
        if($(this).hasClass('opened')){
            while($(this).parents('.details').prev().hasClass('appended')){
                $(this).parents('.details').prev().remove();
            }
            $(this).removeClass('opened');
            return;
        }
        var self = $(this);
        $.ajax({
            url: location.origin + '/get-odds',
            method: 'GET',
            data: {
                match_id: match.attr('data-id'),
                showed_site: match.attr('data-odd-id')
            },
            success: function (res){
                if(res.success){
                    let str = '';
                    $.each(res.data , function (i,v){
                        str += '<tr class="appended"><td></td><td><span>' + v.win_home + '</span></td>';
                        str += '<td><span>' + (!!v.draw ? v.draw : "X") + '</span></td>';
                        str += '<td><span>' + v.win_guest + '</span></td>';
                        str += '<td>'   + v.site_nickname + '</td>';
                        str += '<td><span>' + v.last_update + '</span></td></tr>';
                    })
                    $('tr.appended').remove();
                    $('.details a').removeClass('opened');
                    $(self).addClass('opened');
                    $(match).after(str);
                }
            }
        })
    });
});
