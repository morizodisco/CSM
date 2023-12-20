@extends('common.layout')
@extends('common.menu')
@section('content')
    <div id="report_wrap">
        <header>
            <div class="left">
                <div class="arrow pre"></div>
                <select name="select_month" class="datepickre" onchange="submit(this.form)" form="form_filter">
                    @foreach($select_month as $select_item)
                        <option
                            value="{{$select_item}}" {{ ($select_item == request()->select_month) ? 'selected' : '' }}>{{str_replace("-"," / ","$select_item")}}</option>
                    @endforeach
                </select>
                <div class="arrow next"></div>
                <div class="select_wrap">
                    <select name="user_id" onchange="submit(this.form)" form="form_filter">
                        <option value="">担当で絞る</option>
                        @foreach($users as $user)
                            <option
                                value="{{$user->id}}" {{ ($user->id == request()->user_id) ? 'selected' : '' }}>{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
                <form method="get" id="form_filter">@csrf</form>
            </div>
            <div class="right">
                SALES REPORT
            </div>
        </header>
        <div class="main_wrap">
            <div class="content_wrap">
                <div class="main_content">
                    <div class="table_position">
                        <div class="table_wrap left syncscroll" name="myElements">
                            <table>
                                <thead>
                                <tr>
                                    <th rowspan="9" class="vertical_head_1">
                                        <div class="inline"><br></div>
                                    </th>
                                    <th colspan="5" class="side_head">
                                        <div class="inline"></div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">前月利益合計</div>
                                    </th>
                                    <th colspan="3">
                                        <div class="inline">前月比 ( 予想 )</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">￥ {{ number_format($aggregated['profit_last_month']) }}</div>
                                    </th>
                                    <th colspan="3" class="yellow_text">
                                        <div class="inline">{{ number_format($aggregated['profit_rate'], 2) }} %</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="5">
                                        <div class="inline">日 - 利益達成率</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="red_text">
                                        <div class="inline">- %</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">日 - 目標利益</div>
                                    </th>
                                    <th colspan="3">
                                        <div class="inline">日 - 平均利益</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">￥ -</div>
                                    </th>
                                    <th colspan="3">
                                        <div class="inline">￥ {{ number_format($aggregated['avg_profit']) }}</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">月 - 予想利益</div>
                                    </th>
                                    <th colspan="3">
                                        <div class="inline">月 - 現在利益</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">￥ {{ number_format($aggregated['expected_profit']) }}</div>
                                    </th>
                                    <th colspan="3">
                                        <div class="inline">￥ {{ number_format($aggregated['profit']) }}</div>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        <div class="inline">日付</div>
                                    </th>
                                    <th>
                                        <div class="inline">売上</div>
                                    </th>
                                    <th>
                                        <div class="inline">広告費</div>
                                    </th>
                                    <th>
                                        <div class="inline small_cell">利益</div>
                                    </th>
                                    <th>
                                        <div class="inline small_cell">利益率</div>
                                    </th>
                                    <th>
                                        <div class="inline small_cell">ROAS</div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="line">
                                    <td colspan="6">
                                        <div class="inline"></div>
                                    </td>
                                </tr>
                                @foreach($period as $day)
                                    <tr>
                                        <td class="date {{ $week[$day->format('w')]['class'] }}">
                                            <div class="inline">{{ $day->format('m/d') }}
                                                ({{ $week[$day->format('w')]['day'] }})
                                            </div>
                                        </td>
                                        <td class="blue_text">
                                            <div class="inline">￥ {{ number_format($aggregated[$day->format('d')]['sales']) }}</div>
                                        </td>
                                        <td class="red_text">
                                            <div class="inline">￥ {{ number_format($aggregated[$day->format('d')]['cost']) }}</div>
                                        </td>
                                        <td class="yellow_text">
                                            <div class="inline">￥ {{ number_format($aggregated[$day->format('d')]['profit']) }}</div>
                                        </td>
                                        <td class="orange_text">
                                            <div class="inline">{{ number_format($aggregated[$day->format('d')]['profit_rate'], 2) }}%</div>
                                        </td>
                                        <td class="orange_text">
                                            <div class="inline">{{ number_format($aggregated[$day->format('d')]['roas'], 2) }}%</div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="table_wrap right syncscroll" name="myElements">
                            <div class="table_position">
                                <ul>
                                    @foreach($genre_list as $genre)
                                        <li>
                                            <table>
                                                <thead>
                                                <tr>
                                                    <th colspan="4" class="side_head">
                                                        <div class="inline">
                                                            <div class="side_title"><span>{{ $genre->name }}</span></div>
                                                            <div class="show_detail"><a href="/report/detail/{{ $genre->id }}">個別で見る</a></div>
                                                            <div class="note write_allowed">
                                                                <textarea name="note" placeholder="備考をここに記入してください"
                                                                          class="genre_note"
                                                                          data-genre_id="{{$genre->id}}">{{$genre->note}}</textarea>
                                                            </div>
                                                        </div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">
                                                        <div class="inline">前月利益合計</div>
                                                    </th>
                                                    <th colspan="3">
                                                        <div class="inline">前月比 ( 予想 )</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">
                                                        <div class="inline">￥ {{ number_format($genre->get_header_aggregated($year, $month)['profit_last_month']) }}</div>
                                                    </th>
                                                    <th colspan="3" class="yellow_text">
                                                        <div class="inline">{{ number_format($genre->get_header_aggregated($year, $month)['profit_rate'], 2) }} %</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5">
                                                        <div class="inline">日 - 利益達成率</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="5" class="red_text">
                                                        <div class="inline">- %</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">
                                                        <div class="inline">日 - 目標利益</div>
                                                    </th>
                                                    <th colspan="3">
                                                        <div class="inline">日 - 平均利益</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">
                                                        <div class="inline">￥ -</div>
                                                    </th>
                                                    <th colspan="3">
                                                        <div class="inline">￥ {{ number_format($genre->get_header_aggregated($year, $month)['avg_profit']) }}</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">
                                                        <div class="inline">月 - 予想利益</div>
                                                    </th>
                                                    <th colspan="3">
                                                        <div class="inline">月 - 現在利益</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">
                                                        <div class="inline">￥ {{ number_format($genre->get_header_aggregated($year, $month)['expected_profit']) }}</div>
                                                    </th>
                                                    <th colspan="3">
                                                        <div class="inline">￥ {{ number_format($genre->get_header_aggregated($year, $month)['profit']) }}</div>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <div class="inline small_cell">成果数</div>
                                                    </th>
                                                    <th>
                                                        <div class="inline">売上</div>
                                                    </th>
                                                    <th>
                                                        <div class="inline">広告費</div>
                                                    </th>
                                                    <th>
                                                        <div class="inline">利益</div>
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="line">
                                                    <td colspan="4">
                                                        <div class="inline"></div>
                                                    </td>
                                                </tr>
                                                @foreach($period as $day)
                                                    <tr>
                                                        <td>
                                                            <div class="inline text_right">{{ $genre->get_aggregated($year, $month)[$day->format('d')]->confirm_num ?? 0 }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="inline">￥ {{ number_format($genre->get_aggregated($year, $month)[$day->format('d')]->confirm_price ?? 0) }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="inline">￥ {{ number_format(($genre->get_total_aggregated($year, $month)[$day->format('d')]->add_cost ?? 0) * 1.1) }}</div>
                                                        </td>
                                                        <td class="yellow_text">
                                                            <div class="inline">￥ {{ number_format(($genre->get_aggregated($year, $month)[$day->format('d')]->confirm_price ?? 0) - (($genre->get_total_aggregated($year, $month)[$day->format('d')]->add_cost ?? 0) * 1.1)) }}</div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

