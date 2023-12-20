//select複数選択化
$(function () {
    $(document).ready(function () {
        $('.multiple_select').select2({
            placeholder: 'クリックしてメディアを選択',
            multiple: true,
            width: 'resolve'
        }).on('select2:opening select2:closing', function () {
            // select2関数を開いたり閉じたりするときに検索ボックスを非表示にします。
            var $searchfield = $(this).parent().find('.select2-search__field');
            $searchfield.prop('disabled', true);
        });
    });
});

//権限編集展開
$(function () {
    $('.editor_open').on('click', function () {
        $(this).parent().parent().next().toggleClass('active');
        $(this).parent().parent().toggleClass('active');
    });
});

//削除アラート
$(function () {
    $('button.delete').on('click', function () {
        const result = window.confirm('この項目を削除しますか？');
        if (result) {
            return true;
        } else {
            return false;
        }
    });
});

//メニュークリックで表示
$(function () {
    $('.menu_btn').on('click', function () {
        if($('#header_menu').hasClass('active')){
            $('.black_back').fadeOut();
            $('#header_menu .menu_list').fadeOut();
            $(this).removeClass('active');
            setTimeout(function(){
                $('#header_menu').removeClass('active');
            },500);
        }else{
            $('.black_back').fadeIn();
            $('#header_menu .menu_list').fadeIn();
            $('#header_menu').addClass('active');
            $(this).addClass('active');
        }
    });
});

//メンバー追加モーダル追加
$(function () {
    $('.add_btn').on('click', function () {
        $('.black_back').fadeIn();
        $('.modal_content').fadeIn();
    });
});

//メンバー追加モーダル追加
$(function () {
    $('#genre_wrap .add_btn').on('click', function () {
        $('.black_back').fadeIn();
        $('.genre_add_modal').fadeIn();
    });
});

//過去ステータス表示
$(function () {
    $('.calendar_btn').on('click', function () {
        $(this).next('.past_month_list').fadeToggle();
    });
});

//黒背景クリックでモーダル非表示
$(function () {
    $('.black_back').on('click', function () {
        $('.black_back').fadeOut();
        $('.modal_content').fadeOut();
        $('#header_menu .menu_list').fadeOut();
        $('.genre_add_modal').fadeOut();
        $('.menu_btn').removeClass('active');
        setTimeout(function(){
            $('#header_menu').removeClass('active');
        },500);
    });
});

//画像ファイルリアルタイム確認
$(function () {
    $('input[type=file]').change(function () {
        const file = $(this).prop('files')[0];
        const input = $(this);
        if (!file.type.match('image.*')) {
            $(this).val('');
            return;
        }
        const reader = new FileReader();
        reader.onload = function () {
            input.parent().prev().attr('src', reader.result);
        };
        reader.readAsDataURL(file);
    });
});

//未入力フォームの背景色変更
$(function () {
    $('.code_name,.code_genre').on('keydown keyup keypress change focus blur', function () {
        if ($(this).val() == '' || $(this).val() == null) {
            $(this).css({backgroundColor: '#230D0D'});
        } else {
            $(this).css({backgroundColor: '#1A1A1A'});
        }
    }).change();
});

//カレンダー
$(function () {
    $('.datepicker').datepicker();
});

//カレンダー 月だけ
$(function () {
    $('.datepickre_month').datepicker({
        format: 'yyyy年mm月',
        language: 'ja',       // カレンダー日本語化のため
        minViewMode : 1
        }
    );
});

//カレンダー 期間選択
$(function () {
    var datepicker_base = $('.datepicker_base').datepicker({
        'language': 'en',
        'dateFormat': 'mm/dd',
        'range': true,
        'multipleDatesSeparator': ' - ',
        'onSelect': function(formattedDate, date, inst) {
            if (date[0] !== undefined) {
                var d = new Date(date[0]);
                var formatted = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
                inst.$el.closest('form').find('input[name="start_date"]').val(formatted);
            } else {
                inst.$el.closest('form').find('input[name="start_date"]').val('');
            }
            if (date[1] !== undefined) {
                var d = new Date(date[1]);
                var formatted = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
                inst.$el.closest('form').find('input[name="end_date"]').val(formatted);
            } else {
                inst.$el.closest('form').find('input[name="end_date"]').val('');
            }
        },
        'onShow': function(inst, animationCompleted) {
            //console.log(inst.$el.attr('data-start_date'));
            inst.init_base_data = inst.$el.val();

            var start_date = inst.$el.attr('data-start_date');
            var end_date = inst.$el.attr('data-end_date');
            if (start_date.length != 0 && end_date.length) inst.selectDate([new Date(start_date), new Date(end_date)]);
        },
        'onHide': function(inst, animationCompleted) {
            var init_base_data = inst.init_base_data;
            var change_base_data = inst.$el.val();
            console.log(init_base_data);
            console.log(change_base_data);

            if (init_base_data != change_base_data) inst.$el.closest('form').submit();
        }
    }).data('datepicker');

    //var init_base_data = datepicker_base.$el.val();
});

$(function () {
    var datepicker_rate = $('.datepicker_rate').datepicker({
        'language': 'en',
        'dateFormat': 'mm/dd',
        'range': true,
        'multipleDatesSeparator': ' - ',
        'onSelect': function(formattedDate, date, inst) {
            if (date[0] !== undefined) {
                var d = new Date(date[0]);
                var formatted = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
                inst.$el.closest('form').find('input[name="rate_start_date"]').val(formatted);
            } else {
                inst.$el.closest('form').find('input[name="rate_start_date"]').val('');
            }
            if (date[1] !== undefined) {
                var d = new Date(date[1]);
                var formatted = d.getFullYear() + '-' + (d.getMonth()+1) + '-' + d.getDate();
                inst.$el.closest('form').find('input[name="rate_end_date"]').val(formatted);
            } else {
                inst.$el.closest('form').find('input[name="rate_end_date"]').val('');
            }
        },
        'onShow': function(inst, animationCompleted) {
            //console.log(inst.$el.attr('data-start_date'));
            inst.init_rate_data = inst.$el.val();

            var start_date = inst.$el.attr('data-start_date');
            var end_date = inst.$el.attr('data-end_date');
            if (start_date.length != 0 && end_date.length) inst.selectDate([new Date(start_date), new Date(end_date)]);
        },
        'onHide': function(inst, animationCompleted) {
            var init_rate_data = inst.init_rate_data;
            var change_rate_data = inst.$el.val();
            console.log(init_rate_data);
            console.log(change_rate_data);

            if (init_rate_data != change_rate_data) inst.$el.closest('form').submit();
        }
    }).data('datepicker');

    //var init_rate_data = datepicker_rate.$el.val();
});


//テーブルホバー
$(function () {
    $('tbody tr').hover(function() {
        //マウスを乗せたら色が変わる
        const day = $(this).data('day');
    });
});

//月選択したら自動でsubmit
$(function(){
    $("select[name=select_month]").change(function(){
        $("#select_month").submit();
    });
});

//メディア選択したら自動でsubmit
$(function(){
    $("select[name=select_genre]").change(function(){
        $("#select_genre").submit();
    });
});

//検索結果をローダー挟んで表示
$(window).on('load', function () {
    setTimeout(function () {
        $('.dot-falling').fadeOut(500);
    }, 500);
    setTimeout(function () {
        $('.loader').fadeOut(1000);
        $('.main_wrap').fadeIn(1000);
    }, 1000);
});


