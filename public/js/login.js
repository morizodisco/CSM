// フォームの位置調整
(function() {
    $(function () {
        h = $(window).height();
        $('.container').css('min-height', h + 'px');
    });

    $(window).on('resize', function(){
        h = $(window).height();
        $('.container').css('min-height', h + 'px');
    });
})();
