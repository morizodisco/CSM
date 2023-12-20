@extends('common.layout')
@extends('common.menu')
@section('content')
    <div id="member_wrap">
        <header>
            <div class="left">
                <span class="add_btn">メンバーを追加</span>
            </div>
            <div class="right">
                MEMBER MANAGER
            </div>
        </header>
        <div class="main_wrap">
            <div class="content_wrap">
                <ul class="member_list">
                    @foreach($users as $user)
                        <li>
                            <form method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="top">
                                    <div class="left">
                                        <ul>
                                            <li class="icon">
                                                <img src="{{ (!empty($user->img_path)) ? '/storage/'.$user->img_path : '/images/no_img.jpg' }}">
                                                <label>
                                                    <input type="file" name="image_path" class="camera">
                                                </label>
                                            </li>
                                            <li class="name"><input name="name" value="{{ $user->name }}"></li>
                                            <li class="email"><input name="email" value="{{ $user->email }}"></li>
                                            <li class="password"><input type="text" name="pre_password" value="{{ $user->pre_password }}"
                                                                        placeholder="変更する場合は入力"></li>
                                            <li>
                                                <div class="select_wrap">
                                                    <select name="authority_id">
                                                        <option value="1" {{ $user->authority_id == 1 ? ' selected' : '' }}>
                                                            管理者
                                                        </option>
                                                        <option value="2" {{ $user->authority_id == 2 ? ' selected' : '' }}>
                                                            メンバー
                                                        </option>
                                                    </select>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="right">
                                        <span class="editor_open">閲覧権限 / 担当 ( 編集権限 ) を選択する</span>
                                        <div class="btn_area">
                                            <input type="hidden" name="id" value="{{ $user->id }}">
                                            <button type="submit" name="update" value="update" class="update"><img
                                                        src="/images/check.svg"></button>
                                            <button type="submit" name="delete" value="delete" class="delete"><img
                                                        src="/images/delete.svg"></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="bottom">
                                    <dl>
                                        <dt>担当</dt>
                                        <dd>
                                            <select name="charge_category[]" class="multiple_select"  multiple="multiple"
                                                    style="width: 100%">
                                                @foreach($available_genre as $genre)
                                                    <option value="{{ $genre->id }}"
                                                    @foreach($user->genres as $genre_select)
                                                        @continue($genre_select->category_type !== 3)
                                                            {{ $genre_select->genre_id == $genre->id ? ' selected' : '' }}
                                                        @endforeach
                                                    >
                                                        {{ $genre->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </dd>
                                        <dt>閲覧権限</dt>
                                        <dd>
                                            @if($user->authority_id === 2)
                                                <select name="display_category[]" class="multiple_select"  multiple="multiple"
                                                        style="width: 100%">
                                                    @foreach($available_genre as $genre)
                                                        <option value="{{ $genre->id }}"
                                                        @foreach($user->genres as $genre_select)
                                                            @continue($genre_select->category_type !== 1)
                                                                    {{ $genre_select->genre_id == $genre->id ? ' selected' : '' }}
                                                                @endforeach
                                                        >
                                                            {{ $genre->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="authority_select">
                                                    管理者権限の為、全メディア閲覧可能です
                                                </div>
                                            @endif
                                        </dd>
                                        <dt>編集権限</dt>
                                        <dd>
                                            @if($user->authority_id === 2)
                                                <select name="edit_category[]" class="multiple_select"  multiple="multiple"
                                                        style="width: 100%">
                                                    @foreach($available_genre as $genre)
                                                        <option value="{{ $genre->id }}"
                                                        @foreach($user->genres as $genre_select)
                                                            @continue($genre_select->category_type !== 2)
                                                                    {{ $genre_select->genre_id == $genre->id ? ' selected' : '' }}
                                                                @endforeach
                                                        >
                                                            {{ $genre->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="authority_select">
                                                    管理者権限の為、全メディア編集可能です
                                                </div>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="member_add_modal modal_content">
            <h2>メンバーを追加</h2>
            <form method="post" enctype="multipart/form-data">
                @csrf
                <div class="main_wrap">
                    <div class="icon">
                        <img src="/images/no_img.jpg">
                        <label>
                            <img src="/images/plus.svg">
                            <input type="file" name="image_path" class="camera">
                        </label>
                    </div>
                    <div class="input_wrap">
                        <input name="name" value="" placeholder="名前を入力してください">
                        <input name="email" value="" placeholder="メールアドレスを入力してください">
                        <div class="select_wrap">
                            <select name="authority_id">
                                <option value="1">メンバー ( 招待後に各種権限を指定してください )</option>
                                <option value="2">管理者</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="btn_wrap">
                    <button type="submit" name="add" value="add">追加</button>
                </div>
            </form>
        </div>
    </div>
@stop
