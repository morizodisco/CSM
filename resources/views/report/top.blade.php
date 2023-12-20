@extends('common.layout')
@extends('common.menu')
@section('content')

    <div class="loader">
        <div class="dot_wrap">
            <div class="dot-falling"></div>
        </div>
    </div>

    <div id="report_wrap">
        <header>
            <div class="left">
                <div class="arrow pre"></div>
                <div class="select_wrap">
                    <select name="select_month" onchange="submit(this.form)" form="form_filter">
                        @foreach($select_month as $select_item)
                            <option
                                    value="{{$select_item}}" {{ ($select_item == request()->select_month) ? 'selected' : '' }}>{{str_replace("-"," / ","$select_item")}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="arrow next"></div>
                @if(Auth::user()->authority_id == 1)
                <div class="select_wrap">
                    <select name="user_id" onchange="submit(this.form)" form="form_filter">
                        <option value="">全ての担当</option>
                        @foreach($users as $user)
                            <option
                                    value="{{$user->id}}" {{ ($user->id == request()->user_id) ? 'selected' : '' }}>{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
                <form method="get" id="form_filter">
                    @csrf
                    <input type="text" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="メディア名を入力">
                    <button type="submit" class="top_header">メディア名で絞り込む</button>
                </form>
                <a href="/report?select_month={{isset($_GET['select_month'])? $_GET['select_month'] : ''}}&user_id={{isset($_GET['user_id'])? $_GET['user_id'] : ''}}&_token={{isset($_GET['_token'])? $_GET['_token'] : ''}}&keyword="
                   class="top_header">すべてのメディアを表示</a>
                    @endif
            </div>
            <div class="right">
                MASTER REPORT
                <div style="display: none">

                </div>
            </div>
        </header>
        <div class="main_wrap" style="display: none">
            <div class="content_wrap">
                <div class="main_content">

                    <form method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="table_wrap left">
                            <div class="table_position">
                                <table>
                                    <thead>
                                    <tr>
                                        <th rowspan="9" class="vertical_head_1">
                                            <div class="inline"><br></div>
                                        </th>
                                        <th colspan="5" class="vertical_head_4">
                                            <div class="inline"></div>
                                        </th>
                                        <th rowspan="10" class="vertical_head_2 border">
                                            <div class="border"></div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="4" class="vertical_head_2">
                                                <div class="inline">
                                                    <div class="side_title">
                                                        <span class="status"
                                                              style="color: {{ $genre->display_color }};"></span>
                                                        <span>{{ $genre->name }}</span>
                                                    </div>
                                                    <div class="show_detail"><a
                                                                href="/report/detail/{{ $genre->id }}">個別で見る</a>
                                                    </div>
                                                    <div class="note write_allowed">
                                                                <textarea name="note" placeholder="備考をここに記入してください"
                                                                          class="genre_note"
                                                                          data-genre_id="{{$genre->id}}">{{$genre->note}}</textarea>
                                                    </div>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline">前月利益合計</div>
                                        </th>
                                        <th colspan="3" class="vertical_head_3 left_2">
                                            <div class="inline">前月比 ( 予想 )</div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="2">
                                                <div class="inline">前月利益合計</div>
                                            </th>
                                            <th colspan="2">
                                                <div class="inline">前月比 ( 予想 )</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline">
                                                ￥ {{isset($last_total)?number_format($last_total['profit']):'-'}} </div>
                                        </th>
                                        <th colspan="3" class="yellow_text vertical_head_3 left_2">
                                            <div class="inline"> {{isset($last_total) && $last_total['profit_rate'] > 0 ? number_format($last_total['profit_rate'],2):'-'}}
                                                %
                                            </div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="2">
                                                <div class="inline">
                                                    ￥ {{isset($genre_total[$genre->id]) && ($genre_total[$genre->id]->expected_profit) ? number_format($genre_total[$genre->id]->profit_last_month):'-' }}</div>
                                            </th>
                                            <th colspan="2" class="yellow_text">
                                                <div class="inline">
                                                    {{isset($genre_total[$genre->id]) && $genre_total[$genre->id]->profit_rate > 0 ?number_format($genre_total[$genre->id]->profit_rate,2):'-'}}
                                                    %
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="vertical_head_3">
                                            <div class="inline">日 - 利益達成率</div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="4">
                                                <div class="inline">日 - 利益達成率</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="red_text vertical_head_3">
                                            @php $target_total = (!empty($all_target) && !empty($main_total->avg_profit))?($main_total->avg_profit / $all_target)*100 : 0 @endphp
                                            <div class="inline {{($target_total > 100) ? 'blue_text' : ''}}">
                                                {{(!empty($target_total))?number_format($target_total,2):'-'}} %
                                            </div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            @php $target[$genre->id] = (!empty($target_profits[$genre->id]->target_profit) && !empty($genre_total[$genre->id]->avg_profit))? ($genre_total[$genre->id]->avg_profit / $target_profits[$genre->id]->target_profit) * 100 : 0 @endphp
                                            <th colspan="4" class="red_text">
                                                <div class="inline {{($target[$genre->id] > 100) ? 'blue_text' : ''}}">
                                                    {{(!empty($target[$genre->id]))?number_format($target[$genre->id],2):'-'}}
                                                    %
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline">日 - 目標利益</div>
                                        </th>
                                        <th colspan="3" class="vertical_head_3 left_2">
                                            <div class="inline">日 - 平均利益</div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="2">
                                                <div class="inline">日 - 目標利益</div>
                                            </th>
                                            <th colspan="2">
                                                <div class="inline">日 - 平均利益</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline">￥ {{number_format($all_target)}}</div>
                                        </th>
                                        <th colspan="3" class="vertical_head_3  left_2">
                                            <div class="inline">
                                                ￥ {{isset($main_total->avg_profit)?number_format($main_total->avg_profit):'-'}} </div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="2">
                                                <div class="inline profit">￥
                                                    <input name="target_profit[{{$genre->id}}]"
                                                           value="{{isset($target_profits[$genre->id])? number_format($target_profits[$genre->id]->target_profit) : null}}">
                                                </div>
                                            </th>
                                            <th colspan="2">
                                                <div class="inline">
                                                    ￥ {{isset($genre_total[$genre->id])?number_format($genre_total[$genre->id]->avg_profit):'-'}}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline">月 - 予想利益</div>
                                        </th>
                                        <th colspan="3" class="vertical_head_3  left_2">
                                            <div class="inline">月 - 現在利益</div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="2">
                                                <div class="inline">月 - 予想利益</div>
                                            </th>
                                            <th colspan="2">
                                                <div class="inline">月 - 現在利益</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline">
                                                ￥ {{isset($main_total->expected_profit)? number_format($main_total->expected_profit):'-'}} </div>
                                        </th>
                                        <th colspan="3" class="vertical_head_3 left_2">
                                            <div class="inline">
                                                ￥ {{isset($main_total->profit)? number_format($main_total->profit):'-'}} </div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th colspan="2">
                                                <div class="inline">
                                                    ￥ {{isset($genre_total[$genre->id])?number_format($genre_total[$genre->id]->expected_profit):'-'}}</div>
                                            </th>
                                            <th colspan="2">
                                                <div class="inline">
                                                    ￥ {{isset($genre_total[$genre->id])?number_format($genre_total[$genre->id]->profit):'-'}}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th class="vertical_head_3">
                                            <div class="inline">日付</div>
                                        </th>
                                        <th class="vertical_head_3 left_0">
                                            <div class="inline" style="width: 94px;">売上</div>
                                        </th>
                                        <th class="vertical_head_3 left_1">
                                            <div class="inline" style="width: 92px;">広告費</div>
                                        </th>
                                        <th class="vertical_head_3 left_2">
                                            <div class="inline small_cell" style="width: 90px">利益</div>
                                        </th>
                                        <th class="vertical_head_3 left_3" style="width: 75px">
                                            <div class="inline small_cell">利益率</div>
                                        </th>
                                        <th class="vertical_head_3 left_4" style="width: 75px">
                                            <div class="inline small_cell">ROAS</div>
                                        </th>
                                        @foreach($genre_list as $genre)
                                            <th>
                                                <div class="inline small_cell">成果数</div>
                                            </th>
                                            <th>
                                                <div class="inline" style="width: 90px;">売上</div>
                                            </th>
                                            <th>
                                                <div class="inline" style="width: 90px;">広告費</div>
                                            </th>
                                            <th>
                                                <div class="inline" style="width: 90px;">利益</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    @php $width = count($genre_list) * 4 @endphp
                                    <tr class="line">
                                        <th colspan="{{ $width + 6 }}">
                                            <div class="inline"></div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($period as $day)
                                        <tr{{ ($day->format('Y-m-d') > date('Y-m-d')) ? ' class=disabled' : '' }}>
                                            <th class="date {{ $week[$day->format('w')]['class'] }}">
                                                <div class="inline">{{ $day->format('m/d') }}
                                                    ({{ $week[$day->format('w')]['day'] }})
                                                </div>
                                            </th>
                                            <td class="blue_text fixed left_0">
                                                <div class="inline">
                                                    ￥ {{ !empty($aggregated[$day->format('d')]['sales']) ? number_format($aggregated[$day->format('d')]['sales']) : 0 }}</div>
                                            </td>
                                            <td class="red_text fixed left_1">
                                                <div class="inline">
                                                    ￥ {{ !empty($aggregated[$day->format('d')]['cost']) ? number_format($aggregated[$day->format('d')]['cost']) : 0 }}</div>
                                            </td>
                                            <td class="yellow_text fixed left_2">
                                                <div class="inline">
                                                    ￥ {{ !empty($aggregated[$day->format('d')]['profit']) ? number_format($aggregated[$day->format('d')]['profit']) : 0 }}</div>
                                            </td>
                                            <td class="orange_text fixed left_3">
                                                <div
                                                        class="inline">{{ !empty($aggregated[$day->format('d')]['sales'] ) ? number_format(($aggregated[$day->format('d')]['profit'] / $aggregated[$day->format('d')]['sales'] * 100), 2) : '0.00' }}
                                                    %
                                                </div>
                                            </td>
                                            <td class="orange_text fixed left_4">
                                                <div
                                                        class="inline">{{ !empty($aggregated[$day->format('d')]['cost'] ) ? number_format(($aggregated[$day->format('d')]['sales'] / $aggregated[$day->format('d')]['cost'] * 100), 2) : '0.00' }}
                                                    %
                                                </div>
                                            </td>
                                            @if($loop->first)
                                                <td rowspan="{{$day_count}}" class="border">
                                                    <div style="height: {{33 * $day_count + 4}}px"></div>
                                                </td>
                                            @endif
                                            @foreach($genre_list as $genre)
                                                <td>
                                                    <div class="inline text_right">
                                                        {{ $genre->get_aggregated($year, $month)[$day->format('d')]['confirm_num'] ?? 0 }}</div>
                                                </td>
                                                <td>
                                                    <div class="inline">
                                                        ￥ {{ number_format($genre->get_aggregated($year, $month)[$day->format('d')]['confirm_price'] ?? 0) }}</div>
                                                </td>
                                                <td>
                                                    <div class="inline">
                                                        ￥ {{ number_format(($genre->get_total_aggregated($year, $month)[$day->format('d')]->add_cost ?? 0)) }}</div>
                                                </td>
                                                @php
                                                    $profit = ($genre->get_aggregated($year, $month)[$day->format('d')]['confirm_price'] ?? 0) - (($genre->get_total_aggregated($year, $month)[$day->format('d')]->add_cost ?? 0) * 1.1);
                                                    if ($profit == 0) $color_class = '';
                                                    elseif ($profit > 0) $color_class = 'blue_text';
                                                    elseif ($profit < 0) $color_class = 'red_text';
                                                @endphp
                                                <td class="{{ $color_class ?? '' }}">
                                                    <div class="inline profit">
                                                        ￥ {{ number_format($profit) }}</div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    <tr class="line">
                                        <td class="fixed" colspan="{{ $width + 7 }}">
                                            <div class="inline"></div>
                                        </td>
                                    </tr>
                                    <tr class="total">
                                        <td class="date">
                                            <div class="inline">集計結果</div>
                                        </td>
                                        <td class="blue_text fixed left_0">
                                            <div class="inline">
                                                ￥ {{ number_format($all_aggregated['sales']) }}</div>
                                        </td>
                                        <td class="red_text fixed left_1">
                                            <div class="inline">
                                                ￥ {{ number_format($all_aggregated['cost']) }}</div>
                                        </td>
                                        <td class="yellow_text fixed left_2">
                                            <div class="inline">
                                                ￥ {{ !empty($main_total->profit) ? number_format($main_total->profit) : '' }}</div>
                                        </td>
                                        <td class="orange_text fixed left_3">
                                            <div class="inline">
                                                {{ !empty($all_aggregated['sales'] ) ? number_format(($all_aggregated['profit'] / $all_aggregated['sales'] * 100), 2) : '0.00' }}
                                                %
                                            </div>
                                        </td>
                                        <td class="orange_text fixed left_4">
                                            <div class="inline">
                                                {{ !empty($all_aggregated['cost'] ) ? number_format(($all_aggregated['sales'] / $all_aggregated['cost'] * 100), 2) : '0.00' }}
                                                %
                                            </div>
                                        </td>
                                        <td class="border">
                                            <div style="height: 33px"></div>
                                        </td>
                                        @foreach($genre_list as $genre)
                                            <td>
                                                <div class="inline text_right">{{ number_format($code_total[$genre->id]['confirm_num'])}}</div>
                                            </td>
                                            <td>
                                                <div class="inline">
                                                    ￥ {{ number_format($code_total[$genre->id]['confirm_price'])}}</div>
                                            </td>
                                            <td>
                                                <div class="inline">
                                                    ￥ {{ number_format($code_total[$genre->id]['add_cost'])}}</div>
                                            </td>
                                            @php
                                                if (number_format($code_total[$genre->id]['profit']) == 0) $color_class = '';
                                                elseif (number_format($code_total[$genre->id]['profit']) > 0) $color_class = 'blue_text';
                                                elseif (number_format($code_total[$genre->id]['profit']) < 0) $color_class = 'red_text';
                                            @endphp
                                            <td class="{{$color_class}}">
                                                <div class="inline profit">
                                                    ￥ {{ number_format($code_total[$genre->id]['profit'])}}</div>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @if($year.'-'.$month != date('Y-m'))
                                        <tr class="total confirm">
                                            <td class="date">
                                                <div class="inline">確定数値</div>
                                            </td>
                                            <td class="blue_text fixed left_0">
                                                <div class="inline">
                                                    ￥
                                                </div>
                                            </td>
                                            <td class="red_text fixed left_1">
                                                <div class="inline">
                                                    ￥
                                                </div>
                                            </td>
                                            <td class="yellow_text fixed left_2">
                                                <div class="inline">
                                                    ￥
                                                </div>
                                            </td>
                                            <td class="orange_text fixed left_3">
                                                <div class="inline">
                                                    %
                                                </div>
                                            </td>
                                            <td class="orange_text fixed left_4">
                                                <div class="inline">
                                                    %
                                                </div>
                                            </td>
                                            <td class="border">
                                                <div style="height: 70px"></div>
                                            </td>
                                            @foreach($genre_list as $genre)
                                                <td>
                                                    <div class="inline text_right">{{number_format($all_adjustments[$genre->id]['confirm_num'])}}</div>
                                                </td>
                                                <td>
                                                    <div class="inline">
                                                        ￥{{number_format($all_adjustments[$genre->id]['confirm_price'])}}</div>
                                                </td>
                                                <td>
                                                    <div class="inline">
                                                        ￥{{number_format($all_adjustments[$genre->id]['add_cost'])}}</div>
                                                </td>
                                                @php
                                                    if (number_format($all_adjustments[$genre->id]['profit']) == 0) $color_class = '';
                                                    elseif (number_format($all_adjustments[$genre->id]['profit']) > 0) $color_class = 'blue_text';
                                                    elseif (number_format($all_adjustments[$genre->id]['profit']) < 0) $color_class = 'red_text';
                                                @endphp
                                                <td class="{{$color_class}}">
                                                    <div class="inline profit">
                                                        ￥{{number_format($all_adjustments[$genre->id]['profit'])}}</div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="year" value="{{ $year }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <button type="submit" name="table_update" value="update"
                            @if(Auth::user()->authority_id == 2)
                                style="left: 215px;"
                                @endif
                            >表を更新</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
