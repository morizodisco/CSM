<html lang="ja">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <title>@yield('title', 'SALES MASTER')</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@900&display=swap" rel="stylesheet">
    <link href="/css/default.css" rel="stylesheet" type="text/css">
    {{--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.min.css">--}}
    <link href="/css/datepicker.min.css" rel="stylesheet" type="text/css">
    <link href="/css/three-dots.css?{{ date('Ymd-Hi') }}" rel="stylesheet" type="text/css">
    <link href="/css/style.css?{{ date('Ymd-Hi') }}" rel="stylesheet" type="text/css">
    <link href="/css/select2.css?{{ date('Ymd-Hi') }}" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="/js/syncscroll.js"></script>
    <script src="/js/select2.js"></script>
    <script src="/js/datepicker.min.js"></script>
    <script src="/js/i18n/datepicker.en.js"></script>
    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>--}}
    {{--<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>--}}
    <script src="https://cdn.it-the-best.com/jquery/plugin/listmousedrag/2.6.1/listmousedragscroll.min.js"></script>
    <script src="/js/common.js?{{ date('Ymd-Hi') }}"></script>
    <script src="/js/ajax.js?{{ date('Ymd-Hi') }}"></script>
    @stack('css')
    @stack('script')
</head>
<body>
@yield('menu')
<div id="main_content">
    @yield('content')
</div>
@yield('footer')
<div class="black_back"></div>
</body>
</html>
