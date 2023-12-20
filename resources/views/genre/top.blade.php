@extends('common.layout')
@extends('common.menu')
@section('content')
    <div id="genre_wrap">
        <header>
            <div class="left">
                <span class="add_btn">メディアを追加</span>
                <input type="text" placeholder="メディア名で検索(まだ使えません)">
            </div>
            <div class="right">
                MEDIA MANAGER
            </div>
        </header>
        <div class="main_wrap">
            <div class="content_wrap">
                <ul class="genre_list">
                    @foreach($genres as $genre)
                        @php
                            if(Auth::user()->authority_id == 2){
                               if(in_array($genre->id, $edit_genres)){
                                   $edit_authority = true;
                               }else{
                                   $edit_authority = false;
                               }
                            }else{
                                $edit_authority = true;
                            }
                        @endphp
                        <li>
                            <form method="post">
                                @csrf
                                <ul>
                                    <li style="{{ ($edit_authority == false)? 'pointer-events: none;opacity: 0.3' : '' }}">
                                        <div class="switch_wrap">
                                            <input type="checkbox" id="menu_{{ $genre->id }}" name="status_flag"
                                                   class="menu_status" {{ $genre->status_flag == 1 ? 'checked' : '' }} />
                                            <label for="menu_{{ $genre->id }}"
                                                   class="menu_status_label"></label>
                                        </div>
                                    </li>
                                    <li class="name" style="width: 15%;">
                                        <input type="text" name="name" value="{{ $genre->name }}">
                                    </li>
                                    <li class="media_id">
                                        <input type="text" name="media_id" value="{{ $genre->media_id }}">
                                    </li>
                                    <li class="note">
                                        <input type="text" name="note" value="{{ $genre->note }}"
                                               placeholder="備考がある場合は記入してください">
                                    </li>
                                    <li style="width: 17%;">
                                        <input type="text" name="google_ads_customer_id" value="{{ $genre->google_ads_customer_id }}"
                                               placeholder="Google広告のお客様IDを入力してください">
                                    </li>
                                    <li style="width: 8%;">
                                        <input type="number" name="display_num" value="{{ $genre->display_num }}" placeholder="表示優先度">
                                    </li>
                                    <li>
                                        <input type="color" name="display_color" value="{{ $genre->display_color }}">
                                    </li>
                                    <li class="code">
                                        登録されているコード数
                                        <span class="code_num">{{ (!empty($code_data[$genre->id])) ? $code_data[$genre->id] : '0' }}</span>
                                    </li>
                                    <li style="{{ ($edit_authority == false)? 'pointer-events: none;opacity: 0.3' : '' }}">
                                        <div class="btn_area">
                                            <input type="hidden" name="id" value="{{ $genre->id }}">
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
        <div class="genre_add_modal modal_content">
            <h2>メディアを追加</h2>
            <form method="post" enctype="multipart/form-data">
                @csrf
                <div class="main_wrap">
                    <div class="input_wrap">
                        <input name="name" value="" placeholder="名前を入力してください" required>
                        <input name="media_id" value="" placeholder="メディアIDを入力してください">
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
