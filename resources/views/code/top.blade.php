@extends('common.layout')
@extends('common.menu')
@section('content')
    <div id="member_wrap">
        <header>
            <div class="left">
                <span class="add_btn">コードを追加</span>
                <form id="select_genre" method="get">
                    @csrf
                    <div class="select_wrap">
                        <select name="select_genre">
                            @foreach($available_genre as $genre)
                                <option value="{{$genre->id}}" {{ $select_genre == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="right">
                CODE MANAGER
            </div>
        </header>
        <div class="main_wrap">
            @php
                if(Auth::user()->authority_id == 2){
                   if(in_array($select_genre, $edit_genres)){
                       $edit_authority = true;
                   }else{
                       $edit_authority = false;
                   }
                }else{
                    $edit_authority = true;
                }
            @endphp
            <div class="content_wrap">
                <div id="code_wrap">
                    <div class="linking_list">
                        <table>
                            <tr>
                                <th style="width: 3%">稼働状況</th>
                                <th style="width: 2%">ADRIP 連携</th>
                                <th style="width: 14%">識別コード</th>
                                <th style="width: 10%">メディア</th>
                                <th style="width: 10%">プロモーション名</th>
                                <th style="width: 8%">単価</th>
                                <th style="width: 13%">管理画面URL</th>
                                <th style="width: 13%">請求先</th>
                                <th style="width: 16%">備考</th>
                                <th style="width: 6%">表示順</th>
                                <th style="width: 5%"></th>
                            </tr>
                            @foreach($codes as $code)
                                <tr>
                                    <td>
                                        <div class="switch_wrap" style="{{ ($edit_authority == false)? 'pointer-events: none;opacity: 0.3' : '' }}">
                                            <input type="checkbox" id="menu_{{ $code->id }}" form="form_{{ $code->id }}"
                                                   name="status_flag"
                                                   class="menu_status" {{ $code->status_flag == 1 ? 'checked' : '' }} />
                                            <label for="menu_{{ $code->id }}"
                                                   class="menu_status_label"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="switch_wrap disabled" style="{{ ($edit_authority == false)? 'pointer-events: none;opacity: 0.3' : '' }}">
                                            <input type="checkbox" id="disabled_{{ $code->id }}"
                                                   form="form_{{ $code->id }}" name="scraping_disabled"
                                                   class="menu_status" {{ !empty($code->scraping_disabled) ? 'checked' : '' }} />
                                            <label for="disabled_{{ $code->id }}"
                                                   class="menu_status_label"></label>
                                        </div>
                                    </td>
                                    <td>{{ $code->code }}</td>
                                    <td>
                                        @if($code->genre_id !== null)
                                            {{ $genres[$code->genre_id]->name }}
                                        @else
                                            <a href="/genre">メディアを登録</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($code->name !== null)
                                            {{ $promotions[$code->name]['name'] ?? '' }}
                                        @else
                                            <a href="/promotion">プロモーションを登録</a>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" name="unit_price" value="{{ $code->unit_price }}"
                                               placeholder="単価を入力してください"
                                               form="form_{{ $code->id }}">
                                    </td>
                                    <td>
                                        <input name="management_url" value="{{ $code->management_url }}"
                                               placeholder="管理画面URLを入力してください"
                                               form="form_{{ $code->id }}">
                                    </td>
                                    <td>
                                        <input name="billing_address" value="{{ $code->billing_address }}"
                                               placeholder="請求先を入力してください"
                                               form="form_{{ $code->id }}">
                                    </td>
                                    <td>
                                        <input name="note" value="{{ $code->note }}" placeholder="備考があれば入力してください"
                                               form="form_{{ $code->id }}">
                                    </td>
                                    <td>
                                        <input type="number" name="display_num" value="{{ $code->display_num }}"
                                               placeholder="表示優先度"
                                               form="form_{{ $code->id }}">
                                    </td>
                                    <td>
                                        <input type="hidden" name="id" value="{{ $code->id }}"
                                               form="form_{{ $code->id }}">
                                        <button type="submit" name="action" form="form_{{ $code->id }}"
                                                value="code_update" style="{{ ($edit_authority == false)? 'pointer-events: none;opacity: 0.3' : '' }}">
                                            <img src="/images/check.svg">
                                        </button>
                                        <form action="" method="post" id="form_{{ $code->id }}">@csrf</form>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="genre_add_modal modal_content">
            <h2>コードを追加</h2>
            <form method="post" enctype="multipart/form-data">
                @csrf
                <div class="main_wrap">
                    <div class="input_wrap">
                        <select name="genre_id">
                            <option value="0">-- メディアを選択してください --</option>
                            @foreach($available_genre as $genre)
                                <option value="{{$genre->id}}">{{ $genre->name }}</option>
                            @endforeach
                        </select>
                        <select name="name">
                            <option value="0">-- プロモーション名を選択してください --</option>
                            @foreach($available_promotion as $promotion)
                                <option value="{{$promotion->id}}">{{ $promotion->name }}</option>
                            @endforeach
                        </select>
                        <input name="unit_price" value="" placeholder="単価を入力してください">
                        <input name="management_url" value="" placeholder="管理画面URLを入力してください">
                        <input name="billing_address" value="" placeholder="請求先を入力してください">
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
