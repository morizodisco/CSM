@extends('common.layout')
@extends('common.menu')
@section('content')
    <div id="promotion_wrap">
        <header>
            <div class="left">
                <span class="add_btn">プロモーションを追加</span>
            </div>
            <div class="right">
                PROMOTION MANAGER
            </div>
        </header>
        <div class="main_wrap">
            <div class="content_wrap">
                <ul class="genre_list">
                    @foreach($promotion_list as $promotion)
                        <li>
                            <form method="post">
                                @csrf
                                <ul>
                                    <li>
                                        <div class="switch_wrap">
                                            <input type="checkbox" id="menu_{{ $promotion->id }}" name="status_flag"
                                                   class="menu_status" {{ $promotion->status_flag == 1 ? 'checked' : '' }} />
                                            <label for="menu_{{ $promotion->id }}"
                                                   class="menu_status_label"></label>
                                        </div>
                                    </li>
                                    <li class="name">
                                        <input type="text" name="name" value="{{ $promotion->name }}">
                                    </li>
                                    <li class="media_id">
                                        <input type="text" name="code" value="{{ $promotion->code }}">
                                    </li>
                                    <li>
                                        <input type="text" name="site_url" value="{{ $promotion->site_url }}"
                                               placeholder="URL">
                                    </li>
                                    <li class="note">
                                        <input type="text" name="note" value="{{ $promotion->note }}"
                                               placeholder="備考がある場合は記入してください">
                                    </li>
                                    <li>
                                        <div class="btn_area">
                                            <input type="hidden" name="id" value="{{ $promotion->id }}">
                                            <button type="submit" name="update" value="update" class="update"><img
                                                        src="/images/check.svg"></button>
                                            <button type="submit" name="delete" value="delete" class="delete"><img
                                                        src="/images/delete.svg"></button>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="modal_content">
            <h2>プロモーションを追加</h2>
            <form method="post" enctype="multipart/form-data">
                @csrf
                <div class="main_wrap">
                    <div class="input_wrap">
                        <input name="name" value="" placeholder="名前を入力してください" required>
                        <input name="code" value="" placeholder="プロモーションIDを入力してください">
                        <input name="site_url" value="" placeholder="URLを入力してください">
                        <input name="note" value="" placeholder="備考がある場合は記入してください">
                    </div>
                </div>
                <div class="btn_wrap">
                    <input type="hidden" name="status_flag" value="1">
                    <button type="submit" name="update" value="update">追加</button>
                </div>
            </form>
        </div>
    </div>
@stop
