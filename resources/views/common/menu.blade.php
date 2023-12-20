@section('menu')
<div id="header_menu">
    <div class="menu_btn">
        <div class="btn_wrap">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="menu_list">
        <ul>
            <li class="{{ (preg_match('{^/report}', $_SERVER['REQUEST_URI'])) ? 'active' : '' }}">
                <a href="/report">売上レポート</a>
            </li>
            <li class="black_border{{ (preg_match('{^/code}', $_SERVER['REQUEST_URI'])) ? ' active' : '' }}">
                <a href="/code">コード管理</a>
            </li>
            <li class="white_border{{ (preg_match('{^/promotion}', $_SERVER['REQUEST_URI'])) ? ' active' : '' }}">
                <a href="/promotion">プロモーション管理</a>
            </li>
            <li class="{{ (preg_match('{^/genre}', $_SERVER['REQUEST_URI'])) ? ' active' : '' }}">
                <a href="/genre">メディア管理</a>
            </li>
            @if(Auth::user()->authority_id == 1)
            <li class="black_border{{ (preg_match('{^/member}', $_SERVER['REQUEST_URI'])) ? ' active' : '' }}">
                <a href="/member">メンバー管理</a>
            </li>
            @endif
            <li class="white_border">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><span>{{ __('ログアウト') }}</span></a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                      　style="display: none;">@csrf</form>
            </li>
        </ul>
    </div>
</div>
@stop
