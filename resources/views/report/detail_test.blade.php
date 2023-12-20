@extends('common.layout')
@extends('common.menu')

@section('title', $genres[$genre_id]->name.' | SALES MASTER')

@section('content')

    <?php $time = array('09', '17', '23') ?>
    <?php $display_time = array('09' => '10', '17' => '18', '23' => '00') ?>

    <div id="report_wrap">
        <header>
            <div class="left">
                <a href="/report" class="back_btn">マスターへ戻る</a>
                {{--                <div class="arrow pre">--}}
                {{--                </div>--}}
                <form id="select_month" method="get">
                    @csrf
                    <div class="select_wrap">
                        <select name="select_month">
                            @foreach($select_month as $month)
                                <option
                                    value="{{$month}}" {{ ($month === $selected_month) ? 'selected' : '' }}>{{str_replace("-"," / ","$month")}}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                {{--                <div class="arrow next"></div>--}}
                {{--<div class="btn_wrap">
                    <button type="submit" name="update" value="update" class="add_btn">期間指定を更新する</button>
                </div>--}}
            </div>
            <div class="right">
                MEDIA REPORT ( {{$genres[$genre_id]->name}} )
            </div>
        </header>
        <div class="main_wrap">
            <div class="content_wrap">
                <div class="top_content">
                    <ul>
                        @for ($i = 1; $i <= 5; $i++)
                            <li>
                                <form method="post" id="term_form">
                                    @csrf
                                    @php
                                        // 設定データを取得
                                        $report = $genres[$genre_id]->get_report($current_year.'-'.$current_month.'-01', $i);
                                    @endphp
                                    <div class="date">
                                        <span class="label">比較</span>
                                        <input type="text" name="rate_date" value="{{ (!empty($report->rate_start_date) && !empty($report->rate_end_date)) ? date('m/d', strtotime($report->rate_start_date)) .' - '. date('m/d', strtotime($report->rate_end_date)) : '選択してください' }}" data-start_date="{{ (!empty($report->rate_start_date)) ? date('Y-m-d', strtotime($report->rate_start_date)) : '' }}" data-end_date="{{ (!empty($report->rate_end_date)) ? date('Y-m-d', strtotime($report->rate_end_date)) : '' }}" class="datepicker_rate">

                                        <span class="label">集計</span>
                                        <input type="text" name="base_date" value="{{ (!empty($report->start_date) && !empty($report->end_date)) ? date('m/d', strtotime($report->start_date)) .' - '. date('m/d', strtotime($report->end_date)) : '選択してください' }}" data-start_date="{{ (!empty($report->start_date)) ? date('Y-m-d', strtotime($report->start_date)) : '' }}" data-end_date="{{ (!empty($report->end_date)) ? date('Y-m-d', strtotime($report->end_date)) : '' }}" class="datepicker_base">
                                    </div>
                                    <input type="hidden" name="rate_start_date" value="{{ (!empty($report->rate_start_date)) ? date('Y-n-j', strtotime($report->rate_start_date)) : '' }}">
                                    <input type="hidden" name="rate_end_date" value="{{ (!empty($report->rate_end_date)) ? date('Y-n-j', strtotime($report->rate_end_date)) : '' }}">

                                    <input type="hidden" name="start_date" value="{{ (!empty($report->start_date)) ? date('Y-n-j', strtotime($report->start_date)) : '' }}">
                                    <input type="hidden" name="end_date" value="{{ (!empty($report->end_date)) ? date('Y-n-j', strtotime($report->end_date)) : '' }}">

                                    <input type="hidden" name="report_id" value="{{ $report->id }}">
                                </form>
                                <div class="price">
                                    @php
                                        // 期間毎の集計データを取得
                                        $aggregated = $genres[$genre_id]->get_aggregated_period_rate($current_year.'-'.$current_month.'-01', $i);
                                    @endphp
                                    <ul>
                                        <li>売 上
                                            <p class="main{{($aggregated['confirm_price'] > 0) ? ' blue_text' : ''}}{{($aggregated['confirm_price'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date)) ? ' default_text': '' }}">￥ {{ (empty($report->start_date) || empty($report->end_date)) ? '-' : number_format($aggregated['confirm_price']) }}</p>
                                            <p class="sub{{($aggregated['confirm_price_diff'] > 0) ? ' blue_text' : ''}}{{($aggregated['confirm_price_diff'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? ' default_text': '' }}">{{((!empty($report->start_date) && !empty($report->end_date) && !empty($report->rate_start_date)) && $aggregated['confirm_price_diff'] > 0) ? '+' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? '-' : number_format($aggregated['confirm_price_diff']) }}</p>
                                        </li>
                                        <li>広告費
                                            <p class="main{{($aggregated['add_cost'] > 0) ? ' blue_text' : ''}}{{($aggregated['add_cost'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date)) ? ' default_text': '' }}">￥ {{ (empty($report->start_date) || empty($report->end_date)) ? '-' : number_format($aggregated['add_cost']) }}</p>
                                            <p class="sub{{($aggregated['add_cost_diff'] > 0) ? ' blue_text' : ''}}{{($aggregated['add_cost_diff'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? ' default_text': '' }}">{{((!empty($report->start_date) && !empty($report->end_date) && !empty($report->rate_start_date)) && $aggregated['add_cost_diff'] > 0) ? '+' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? '-' : number_format($aggregated['add_cost_diff']) }}</p>
                                        </li>
                                        <li>利 益
                                            <p class="main yellow_text{{ (empty($report->start_date) || empty($report->end_date)) ? ' default_text': '' }}">￥ {{ (empty($report->start_date) || empty($report->end_date)) ? '-' : number_format($aggregated['profit']) }}</p>
                                            <p class="sub{{($aggregated['profit_diff'] > 0) ? ' blue_text' : ''}}{{($aggregated['profit_diff'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? ' default_text': '' }}">{{((!empty($report->start_date) && !empty($report->end_date) && !empty($report->rate_start_date)) && $aggregated['profit_diff'] > 0) ? '+' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? '-' : number_format($aggregated['profit_diff']) }}</p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="note write_allowed">
                                    <textarea name="minutes" data-report_id="{{ $report->id }}" placeholder="変更点や追加点、結果、改善案等を記載してください" class="report_minutes">{{ $report->minutes }}</textarea>
                                </div>
                            </li>
                        @endfor
                    </ul>
                </div>
                <div class="bottom_content">
                    <div class="table_wrap left syncscroll" name="myElements">
                        <div class="table_position">
                            <table class="">
                                <thead>
                                <tr>
                                    <th colspan="17" class="vertical_head_2">
                                        <div class="inline"></div>
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="inline">日時</div>
                                    </th>
                                    <th>
                                        <div class="inline">成果数</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 80px">売 上</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 100px">広告費</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 80px">利 益</div>
                                    </th>
                                    <th>
                                        <div class="inline">CPC</div>
                                    </th>
                                    <th>
                                        <div class="inline">MCPA</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 54px">ROI</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 74px">CPA</div>
                                    </th>
                                    <th>
                                        <div class="inline">IS</div>
                                    </th>
                                    <th>
                                        <div class="inline">上部</div>
                                    </th>
                                    <th>
                                        <div class="inline">最上部</div>
                                    </th>
                                    <th>
                                        <div class="inline">変更点</div>
                                    </th>
                                    <th>
                                        <div class="inline">考察</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 48px">CL</div>
                                    </th>
                                    <th>
                                        <div class="inline" style="min-width: 57px">CVR</div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="line">
                                    <td colspan="17">
                                        <div class="inline"></div>
                                    </td>
                                </tr>
                                @foreach($period as $day)
                                    @foreach($time as $h)
                                        @php $h_total = (isset($total[$day->format('d')][$h])) ? $total[$day->format('d')][$h] : null @endphp
                                        @php $w_total = (isset($write_logs[$day->format('d')][$h])) ? $write_logs[$day->format('d')][$h] : null @endphp
                                        @php $w_text = (isset($write_texts[$day->format('d')])) ? $write_texts[$day->format('d')] : null @endphp
                                        @php $w_id = isset($w_total) ? $w_total['id'] : '' @endphp
                                        @php $text_id = isset($w_text) ? $w_text['id'] : '' @endphp
                                        <tr class="{{ ($loop->last) ? '' : 'gray' }}{{ ($day->format('Y-m-d') > date('Y-m-d')) ? ' disabled' : '' }}"
                                            data-day="{{ $day->format('m_d') }}">
                                            @if($loop->first)
                                                <th rowspan="3" class="date {{ $week[$day->format('w')]['class'] }}">
                                                    <div class="date">{{ $day->format('m/d') }}
                                                        ({{ $week[$day->format('w')]['day'] }})<br><span
                                                            class="all_time_btn">全時間を見る</span>
                                                    </div>
                                                </th>
                                            @endif
                                            <td>
                                                <div class="inline">{{$display_time[$h]}}</div>
                                            </td>
                                            <td>
                                                <div class="inline">
                                                    <span>{{ (isset($h_total) ? $h_total['confirm_num'] : '' ) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline blue_text">
                                                    <span>￥&thinsp;{{ (isset($h_total) ? number_format($h_total['confirm_price']) : '' ) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline write_allowed">
                                                    <span class="red_text">￥&thinsp;
                                                        <input type="text" name="add_cost"
                                                               value="{{ (isset($w_total) ? number_format($w_total['add_cost']) : '' ) }}"
                                                               data-year="{{$current_year}}"
                                                               data-month="{{$current_month}}"
                                                               data-date="{{$day->format('d')}}" data-time="{{$h}}"
                                                               data-id="{{$w_id}}" data-genre_id="{{$genre_id}}"
                                                               class="red_text code_total" style="width: 60px">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline yellow_text">
                                                    <span>￥&thinsp;{{(isset($w_total) ? number_format($w_total['profit']) : '' )}}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline write_allowed">
                                                    <span>￥&thinsp;
                                                        <input type="text" name="cpc"
                                                               value="{{ (isset($w_total) ? number_format($w_total['cpc']) : '' ) }}"
                                                               data-year="{{$current_year}}"
                                                               data-month="{{$current_month}}"
                                                               data-date="{{$day->format('d')}}" data-time="{{$h}}"
                                                               data-id="{{$w_id}}" data-genre_id="{{$genre_id}}"
                                                               class="code_total">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline write_allowed">
                                                    <span>￥&thinsp;
                                                        <input type="text" name="mcpa"
                                                               value="{{ (isset($w_total) ? number_format($w_total['mcpa']) : '' ) }}"
                                                               data-year="{{$current_year}}"
                                                               data-month="{{$current_month}}"
                                                               data-date="{{$day->format('d')}}" data-time="{{$h}}"
                                                               data-id="{{$w_id}}" data-genre_id="{{$genre_id}}"
                                                               class="code_total">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline green_text"><span>{{(isset($w_total) ? number_format($w_total['roi']) : '' )}}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline green_text">
                                                    <span>￥&thinsp;{{(isset($w_total) ? number_format($w_total['cpa']) : '' )}}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline write_allowed">
                                                    <span>
                                                        <input type="text" name="is_num"
                                                               value="{{ (isset($w_total) ? number_format($w_total['is_num'],2) : '' ) }}"
                                                               data-year="{{$current_year}}"
                                                               data-month="{{$current_month}}"
                                                               data-date="{{$day->format('d')}}" data-time="{{$h}}"
                                                               data-id="{{$w_id}}" data-genre_id="{{$genre_id}}"
                                                               class="code_total">%
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline write_allowed">
                                                   <span>
                                                      <input type="text" name="top_part"
                                                             value="{{ (isset($w_total) ? number_format($w_total['top_part'],2) : '' ) }}"
                                                             data-year="{{$current_year}}"
                                                             data-month="{{$current_month}}"
                                                             data-date="{{$day->format('d')}}" data-time="{{$h}}"
                                                             data-id="{{$w_id}}" data-genre_id="{{$genre_id}}"
                                                             class="code_total">%
                                                   </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline write_allowed">
                                                    <span>
                                                      <input type="text" name="best_part"
                                                             value="{{ (isset($w_total) ? number_format($w_total['best_part'],2) : '' ) }}"
                                                             data-year="{{$current_year}}"
                                                             data-month="{{$current_month}}"
                                                             data-date="{{$day->format('d')}}" data-time="{{$h}}"
                                                             data-id="{{$w_id}}" data-genre_id="{{$genre_id}}"
                                                             class="code_total">%
                                                    </span>
                                                </div>
                                            </td>
                                            @if($loop->first)
                                                <td rowspan="3" class="text_area">
                                                    <div class="inline write_allowed">
                                                        <textarea name="change_point" placeholder="テキスト入力可"
                                                                  class="note_total"
                                                                  data-year="{{$current_year}}"
                                                                  data-month="{{$current_month}}"
                                                                  data-date="{{$day->format('d')}}"
                                                                  data-id="{{$text_id}}"
                                                                  data-genre_id="{{$genre_id}}">{{$w_text['change_point']}}</textarea>
                                                    </div>
                                                </td>
                                                <td rowspan="3" class="text_area">
                                                    <div class="inline write_allowed">
                                                        <textarea name="consideration" placeholder="テキスト入力可"
                                                                  class="note_total"
                                                                  data-year="{{$current_year}}"
                                                                  data-month="{{$current_month}}"
                                                                  data-date="{{$day->format('d')}}"
                                                                  data-id="{{$text_id}}"
                                                                  data-genre_id="{{$genre_id}}">{{$w_text['consideration']}}</textarea>
                                                    </div>
                                                </td>
                                            @endif
                                            <td>
                                                <div class="inline yellow_text">
                                                    <span>{{ (isset($h_total) ? number_format($h_total['access']) : '' ) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline yellow_text">
                                                    <span>{{ (isset($h_total) ? number_format($h_total['cvr'],1) : '' ) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="table_wrap right syncscroll custom_scroll" name="myElements">
                        <div class="table_position">
                            <ul>
                                @foreach($code_list as $code)
                                    <li>
                                        <table>
                                            <thead>
                                            <tr>
                                                <th colspan="4">
                                                    <div class="inline" style="height: auto">
                                                        <p>{!! !empty($code->management_url) ? '<a href="'.$code->management_url.'" target="_blank">' : '' !!}{{ $code->promotion()->value('name') }}{!! !empty($code->management_url) ? '</a>' : '' !!}
                                                            <br><span>{{ !empty($code->billing_address) ? $code->billing_address : '　' }}</span>
                                                        </p>
                                                        <div class="write_allowed">
                                                            <textarea name="note"
                                                                      placeholder="備考をここに記入してください">{{$code->note}}</textarea>
                                                        </div>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <div class="inline">成果数</div>
                                                </th>
                                                <th>
                                                    <div class="inline">CL</div>
                                                </th>
                                                <th>
                                                    <div class="inline">CVR</div>
                                                </th>
                                                <th>
                                                    <div class="inline">売 上</div>
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
                                                @foreach($time as $h)
                                                    @php $h_code = (isset($code_logs[$code['id']][$day->format('d')][$h])) ? $code_logs[$code['id']][$day->format('d')][$h] : null @endphp
                                                    <tr class="{{ ($loop->last) ? '' : 'gray' }}{{ ($day->format('Y-m-d') > date('Y-m-d')) ? ' disabled' : '' }}">
                                                        <td class="confirm_num">
                                                            <div class="inline write_allowed">
                                                                <span><input type="text" name="confirm_num"
                                                                             value="{{ (isset($h_code) ? number_format($h_code['confirm_num']) : '' ) }}"
                                                                             data-year="{{$current_year}}"
                                                                             data-month="{{$current_month}}"
                                                                             data-date="{{$day->format('d')}}"
                                                                             data-time="{{$h}}"
                                                                             data-code_id="{{$code->id}}"
                                                                             class="code_item"></span>
                                                            </div>
                                                        </td>
                                                        <td class="access">
                                                            <div class="inline write_allowed yellow_text">
                                                                <span><input type="text" name="access"
                                                                             value="{{ (isset($h_code) ? number_format($h_code['access']) : '' ) }}"
                                                                             data-year="{{$current_year}}"
                                                                             data-month="{{$current_month}}"
                                                                             data-date="{{$day->format('d')}}"
                                                                             data-time="{{$h}}"
                                                                             data-code_id="{{$code->id}}"
                                                                             class="code_item"></span>
                                                            </div>
                                                        </td>
                                                        <td class="cvr">
                                                            <div class="inline write_allowed yellow_text">
                                                                <span><input type="text" name="cvr"
                                                                             value="{{ (isset($h_code) ? number_format($h_code['cvr'],1) : '' ) }}"
                                                                             data-year="{{$current_year}}"
                                                                             data-month="{{$current_month}}"
                                                                             data-date="{{$day->format('d')}}"
                                                                             data-time="{{$h}}"
                                                                             data-code_id="{{$code->id}}"
                                                                             class="code_item">%</span>
                                                            </div>
                                                        </td>
                                                        <td class="sales">
                                                            <div class="inline write_allowed blue_text">
                                                                <span>￥&thinsp;<input type="text" name="confirm_price"
                                                                                      value="{{ (isset($h_code) ? number_format($h_code['confirm_price']) : '' ) }}"
                                                                                      data-year="{{$current_year}}"
                                                                                      data-month="{{$current_month}}"
                                                                                      data-date="{{$day->format('d')}}"
                                                                                      data-time="{{$h}}"
                                                                                      data-code_id="{{$code->id}}"
                                                                                      class="code_item"></span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
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
@stop

