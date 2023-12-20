@extends('common.layout')
@extends('common.menu')

@section('title', $genres[$genre_id]->name.' | SALES MASTER')

@section('content')

    <?php $time = array('09', '17', '23') ?>
    <?php $display_time = array('09' => '10', '17' => '18', '23' => '00') ?>

    <div class="loader">
        <div class="dot_wrap">
            <div class="dot-falling"></div>
        </div>
    </div>

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
                                        <input type="text" name="rate_date"
                                               value="{{ (!empty($report->rate_start_date) && !empty($report->rate_end_date)) ? date('m/d', strtotime($report->rate_start_date)) .' - '. date('m/d', strtotime($report->rate_end_date)) : '選択してください' }}"
                                               data-start_date="{{ (!empty($report->rate_start_date)) ? date('Y-m-d', strtotime($report->rate_start_date)) : '' }}"
                                               data-end_date="{{ (!empty($report->rate_end_date)) ? date('Y-m-d', strtotime($report->rate_end_date)) : '' }}"
                                               class="datepicker_rate">

                                        <span class="label">集計</span>
                                        <input type="text" name="base_date"
                                               value="{{ (!empty($report->start_date) && !empty($report->end_date)) ? date('m/d', strtotime($report->start_date)) .' - '. date('m/d', strtotime($report->end_date)) : '選択してください' }}"
                                               data-start_date="{{ (!empty($report->start_date)) ? date('Y-m-d', strtotime($report->start_date)) : '' }}"
                                               data-end_date="{{ (!empty($report->end_date)) ? date('Y-m-d', strtotime($report->end_date)) : '' }}"
                                               class="datepicker_base">
                                    </div>
                                    <input type="hidden" name="rate_start_date"
                                           value="{{ (!empty($report->rate_start_date)) ? date('Y-n-j', strtotime($report->rate_start_date)) : '' }}">
                                    <input type="hidden" name="rate_end_date"
                                           value="{{ (!empty($report->rate_end_date)) ? date('Y-n-j', strtotime($report->rate_end_date)) : '' }}">

                                    <input type="hidden" name="start_date"
                                           value="{{ (!empty($report->start_date)) ? date('Y-n-j', strtotime($report->start_date)) : '' }}">
                                    <input type="hidden" name="end_date"
                                           value="{{ (!empty($report->end_date)) ? date('Y-n-j', strtotime($report->end_date)) : '' }}">

                                    <input type="hidden" name="report_id" value="{{ $report->id }}">
                                </form>
                                <div class="price">
                                    @php
                                        // 期間毎の集計データを取得
                                        $aggregated = $genres[$genre_id]->get_aggregated_period_rate($current_year.'-'.$current_month.'-01', $i);
                                    @endphp
                                    <ul>
                                        <li>売 上
                                            <p class="main{{($aggregated['confirm_price'] > 0) ? ' blue_text' : ''}}{{($aggregated['confirm_price'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date)) ? ' default_text': '' }}">
                                                ￥ {{ (empty($report->start_date) || empty($report->end_date)) ? '-' : number_format($aggregated['confirm_price']) }}</p>
                                            <p class="sub{{($aggregated['confirm_price_diff'] > 0) ? ' blue_text' : ''}}{{($aggregated['confirm_price_diff'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? ' default_text': '' }}">{{((!empty($report->start_date) && !empty($report->end_date) && !empty($report->rate_start_date)) && $aggregated['confirm_price_diff'] > 0) ? '+' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? '-' : number_format($aggregated['confirm_price_diff']) }}</p>
                                        </li>
                                        <li>広告費
                                            <p class="main{{($aggregated['add_cost'] > 0) ? ' blue_text' : ''}}{{($aggregated['add_cost'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date)) ? ' default_text': '' }}">
                                                ￥ {{ (empty($report->start_date) || empty($report->end_date)) ? '-' : number_format($aggregated['add_cost']) }}</p>
                                            <p class="sub{{($aggregated['add_cost_diff'] > 0) ? ' blue_text' : ''}}{{($aggregated['add_cost_diff'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? ' default_text': '' }}">{{((!empty($report->start_date) && !empty($report->end_date) && !empty($report->rate_start_date)) && $aggregated['add_cost_diff'] > 0) ? '+' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? '-' : number_format($aggregated['add_cost_diff']) }}</p>
                                        </li>
                                        <li>利 益
                                            <p class="main yellow_text{{ (empty($report->start_date) || empty($report->end_date)) ? ' default_text': '' }}">
                                                ￥ {{ (empty($report->start_date) || empty($report->end_date)) ? '-' : number_format($aggregated['profit']) }}</p>
                                            <p class="sub{{($aggregated['profit_diff'] > 0) ? ' blue_text' : ''}}{{($aggregated['profit_diff'] < 0) ? ' red_text' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? ' default_text': '' }}">{{((!empty($report->start_date) && !empty($report->end_date) && !empty($report->rate_start_date)) && $aggregated['profit_diff'] > 0) ? '+' : ''}}{{ (empty($report->start_date) || empty($report->end_date) || empty($report->rate_start_date) || empty($report->rate_end_date)) ? '-' : number_format($aggregated['profit_diff']) }}</p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="note write_allowed">
                                    <textarea name="minutes" data-report_id="{{ $report->id }}"
                                              placeholder="変更点や追加点、結果、改善案等を記載してください"
                                              class="report_minutes">{{ $report->minutes }}</textarea>
                                </div>
                            </li>
                        @endfor
                    </ul>
                </div>
                <div class="bottom_content">
                    <div class="table_wrap left">
                        <div class="table_position">
                            <form method="post" enctype="multipart/form-data">
                                @csrf
                                <table class="">
                                    <thead>
                                    <tr>
                                        <th colspan="17" class="vertical_head_2 top_left">
                                            <div class="inline"></div>
                                        </th>
                                        <th rowspan="2" class="vertical_head_2 top_left border">
                                            <div></div>
                                        </th>
                                        @foreach($code_list as $code)
                                            <th colspan="4" class="vertical_head_2">
                                                <div class="inline">
                                                    <p>{!! !empty($code->management_url) ? '<a href="'.$code->management_url.'" target="_blank">' : '' !!}{{ $code->promotion()->value('name') }}{!! !empty($code->management_url) ? '</a>' : '' !!}
                                                        <br><span>{{ !empty($code->billing_address) ? $code->billing_address : '　' }}</span>
                                                    </p>
                                                    <div class="write_allowed">
                                                        <textarea name="promotion_note[{{$code->id}}][note]" disabled
                                                                  placeholder="備考をここに記入してください">{{$code->note}}</textarea>
                                                    </div>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="vertical_head_3">
                                            <div class="inline" style="width: 135px">日時</div>
                                        </th>
                                        <th class="vertical_head_3 third">
                                            <div class="inline">成果数</div>
                                        </th>
                                        <th class="vertical_head_3 fourth">
                                            <div class="inline">売 上</div>
                                        </th>
                                        <th class="vertical_head_3 fifth">
                                            <div class="inline">広告費</div>
                                        </th>
                                        <th class="vertical_head_3 sixth">
                                            <div class="inline">利 益</div>
                                        </th>
                                        <th class="vertical_head_3 seventh">
                                            <div class="inline">CPC</div>
                                        </th>
                                        <th class="vertical_head_3 eighth">
                                            <div class="inline">MCPA</div>
                                        </th>
                                        <th class="vertical_head_3 ninth">
                                            <div class="inline">ROI</div>
                                        </th>
                                        <th class="vertical_head_3 tenth">
                                            <div class="inline">CPA</div>
                                        </th>
                                        <th class="vertical_head_3 eleventh">
                                            <div class="inline">IS</div>
                                        </th>
                                        <th class="vertical_head_3 twelfth">
                                            <div class="inline">上部</div>
                                        </th>
                                        <th class="vertical_head_3 thirteenth">
                                            <div class="inline">最上部</div>
                                        </th>
                                        <th class="vertical_head_3 fourteenth">
                                            <div class="inline">変更点</div>
                                        </th>
                                        <th class="vertical_head_3 fifteenth">
                                            <div class="inline">考察</div>
                                        </th>
                                        <th class="vertical_head_3 sixteenth">
                                            <div class="inline">CL</div>
                                        </th>
                                        <th class="vertical_head_3 seventeenth">
                                            <div class="inline">CVR</div>
                                        </th>
                                        @foreach($code_list as $code)
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
                                                <div class="inline confirm_price">売 上</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    @php $width = count($code_list) * 4 @endphp
                                    <tr class="line">
                                        <th colspan="{{ $width + 18 }}">
                                            <div class="inline"></div>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($period as $day)
                                        @foreach($time as $h)
                                            @php $h_total = (isset($total[$day->format('d')][$h])) ? $total[$day->format('d')][$h] : null @endphp
                                            @php $w_total = (isset($write_logs[$day->format('d')][$h])) ? $write_logs[$day->format('d')][$h] : null @endphp
                                            @php if(isset($new_texts[$day->format('d')])){
                                                $w_text = $new_texts[$day->format('d')];}
                                               elseif(isset($write_texts[$day->format('d')])){
                                                $w_text = $write_texts[$day->format('d')];}
                                                else{
                                                   $w_text = null;
                                                }
                                            @endphp
                                            @php $w_id = isset($w_total) ? $w_total['id'] : '' @endphp
                                            @php $text_id = isset($w_text) ? $w_text['id'] : '' @endphp
                                            @php $status = ($day->format('Y-m-d') > date('Y-m-d')) ? 'disabled' : '' @endphp
                                            <tr class="{{ ($loop->last) ? '' : 'gray' }}{{ ($day->format('Y-m-d') > date('Y-m-d')) ? ' disabled' : '' }}"
                                                data-day="{{ $day->format('m_d') }}">
                                                @if($loop->first)
                                                    <th rowspan="3"
                                                        class="date fixed {{ $week[$day->format('w')]['class'] }}">
                                                        <div class="date">{{ $day->format('m/d') }}
                                                            ({{ $week[$day->format('w')]['day'] }})<br>
                                                            <span class="all_time_btn">全時間を見る</span>
                                                        </div>
                                                    </th>
                                                @endif
                                                <td class="fixed second">
                                                    <div class="inline">{{$display_time[$h]}}</div>
                                                </td>
                                                <td class="fixed third">
                                                    <div class="inline">
                                                        <span>{{ (isset($h_total) ? $h_total['confirm_num'] : '' ) }}</span>
                                                    </div>
                                                </td>
                                                <td class="fixed fourth">
                                                    <div class="inline blue_text">
                                                        <span>￥&thinsp;{{ (isset($h_total) ? number_format($h_total['confirm_price']) : '' ) }}</span>
                                                    </div>
                                                </td>
                                                <td class="fixed fifth">
                                                    <div class="inline write_allowed">
                                                    <span class="red_text">￥&thinsp;
                                                        <input type="text"
                                                               name="fill_out[{{$day->format('d')}}][{{$h}}][add_cost]"
                                                               value="{{ (isset($w_total) ? number_format($w_total['add_cost']) : '' ) }}"
                                                               class="red_text code_total" style="width: 60px" disabled>
                                                    </span>
                                                    </div>
                                                </td>
                                                <td class="fixed sixth">
                                                    <div class="inline yellow_text">
                                                        <span>￥&thinsp;{{(isset($w_total) ? number_format($w_total['profit']) : '' )}}</span>
                                                    </div>
                                                </td>
                                                <td class="fixed seventh">
                                                    <div class="inline write_allowed">
                                                    <span>￥&thinsp;
                                                        <input type="text"
                                                               name="fill_out[{{$day->format('d')}}][{{$h}}][cpc]"
                                                               value="{{ (isset($w_total) ? number_format($w_total['cpc']) : '' ) }}"
                                                               class="code_total" disabled>
                                                    </span>
                                                    </div>
                                                </td>
                                                <td class="fixed eighth">
                                                    <div class="inline write_allowed">
                                                    <span>￥&thinsp;
                                                        <input type="text"
                                                               name="fill_out[{{$day->format('d')}}][{{$h}}][mcpa]"
                                                               value="{{ (isset($w_total) ? number_format($w_total['mcpa']) : '' ) }}"
                                                               class="code_total" disabled>
                                                    </span>
                                                    </div>
                                                </td>
                                                <td class="fixed ninth">
                                                    <div class="inline green_text"><span>{{(isset($w_total) ? number_format($w_total['roi']) : '' )}}%</span>
                                                    </div>
                                                </td>
                                                <td class="fixed tenth">
                                                    <div class="inline green_text">
                                                        <span>￥&thinsp;{{(isset($w_total) ? number_format($w_total['cpa']) : '' )}}</span>
                                                    </div>
                                                </td>
                                                <td class="fixed eleventh">
                                                    <div class="inline write_allowed">
                                                    <span>
                                                        <input type="text"
                                                               name="fill_out[{{$day->format('d')}}][{{$h}}][is_num]"
                                                               value="{{ (isset($w_total) ? number_format($w_total['is_num'],2) : '' ) }}"
                                                               class="code_total" disabled>%
                                                    </span>
                                                    </div>
                                                </td>
                                                <td class="fixed twelfth">
                                                    <div class="inline write_allowed">
                                                   <span>
                                                      <input type="text"
                                                             name="fill_out[{{$day->format('d')}}][{{$h}}][top_part]"
                                                             value="{{ (isset($w_total) ? number_format($w_total['top_part'],2) : '' ) }}"
                                                             class="code_total" disabled>%
                                                   </span>
                                                    </div>
                                                </td>
                                                <td class="fixed thirteenth">
                                                    <div class="inline write_allowed">
                                                    <span>
                                                      <input type="text"
                                                             name="fill_out[{{$day->format('d')}}][{{$h}}][best_part]"
                                                             value="{{ (isset($w_total) ? number_format($w_total['best_part'],2) : '' ) }}"
                                                             class="code_total" disabled>%
                                                    </span>
                                                    </div>
                                                </td>
                                                @if($loop->first)
                                                    <td rowspan="3" class="text_area fixed fourteenth">
                                                        <div class="inline write_allowed">
                                                        <textarea name="textarea[{{$day->format('d')}}][change_point]"
                                                                  placeholder="テキスト入力可" {{$status}}
                                                                  class="note_total">{{$w_text['change_point']}}</textarea>
                                                        </div>
                                                    </td>
                                                    <td rowspan="3" class="text_area fixed fifteenth">
                                                        <div class="inline write_allowed">
                                                        <textarea name="textarea[{{$day->format('d')}}][consideration]"
                                                                  placeholder="テキスト入力可" {{$status}}
                                                                  class="note_total">{{$w_text['consideration']}}</textarea>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td class="fixed sixteenth">
                                                    <div class="inline yellow_text">
                                                        <span>{{ (isset($h_total) ? number_format($h_total['access']) : '' ) }}</span>
                                                    </div>
                                                </td>
                                                <td class="fixed seventeenth">
                                                    <div class="inline yellow_text">
                                                        <span>{{ (isset($h_total) ? number_format($h_total['cvr'],1) : '' ) }}%</span>
                                                    </div>
                                                </td>
                                                @if ($loop->first)
                                                    <td rowspan="3" class="fixed border">
                                                        <div></div>
                                                    </td>
                                                @endif
                                                @foreach($code_list as $code)
                                                    @php $h_code = (isset($code_logs[$code['id']][$day->format('d')][$h])) ? $code_logs[$code['id']][$day->format('d')][$h] : null @endphp
                                                    <td class="confirm_num">
                                                        <div class="inline write_allowed">
                                                            <span>
                                                                <input type="text" class="code_item" disabled
                                                                       name="media_figures[{{$code->id}}][{{$day->format('d')}}][{{$h}}][confirm_num]"
                                                                       value="{{ (isset($h_code) ? number_format($h_code['confirm_num']) : '' ) }}">
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="access">
                                                        <div class="inline write_allowed yellow_text">
                                                            <span>
                                                                <input type="text" class="code_item" disabled
                                                                       name="media_figures[{{$code->id}}][{{$day->format('d')}}][{{$h}}][access]"
                                                                       value="{{ (isset($h_code) ? number_format($h_code['access']) : '' ) }}">
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="cvr">
                                                        <div class="inline yellow_text">
                                                            <span>{{ (isset($h_code) ? number_format($h_code['cvr'],1) : '' ) }}%</span>
                                                        </div>
                                                    </td>
                                                    <td class="sales">
                                                        <div class="inline write_allowed blue_text">
                                                            <span>￥&thinsp;
                                                                <input type="text" class="code_item" disabled
                                                                       style="width: 60px"
                                                                       name="media_figures[{{$code->id}}][{{$day->format('d')}}][{{$h}}][confirm_price]"
                                                                       value="{{ (isset($h_code) ? number_format($h_code['confirm_price']) : '' ) }}">
                                                            </span>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    <tr class="line">
                                        <td class="fixed" colspan="{{ $width + 18 }}">
                                            <div class="inline"></div>
                                        </td>
                                    </tr>
                                    <tr class="total">
                                        <td colspan="2" class="date">
                                            <div class="inline">集計結果</div>
                                        </td>
                                        <td class="fixed third">
                                            <div class="inline">
                                                <span>{{ number_format($all_total['confirm_num']) }}</span>
                                            </div>
                                        </td>
                                        <td class="fixed fourth">
                                            <div class="inline blue_text">
                                                <span>￥&thinsp;{{ number_format($all_total['confirm_price'])}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed fifth">
                                            <div class="inline red_text" style="text-align: left">
                                                <span>￥&thinsp;{{ number_format($all_total['add_cost'])}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed sixth">
                                            <div class="inline yellow_text">
                                                <span>￥&thinsp;{{ number_format($all_total['profit'])}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed seventh">
                                            <div class="inline" style="text-align: left">
                                                <span>￥&thinsp;{{ $all_total['cpc']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed eighth">
                                            <div class="inline" style="text-align: left">
                                                <span>￥&thinsp;{{ $all_total['mcpa']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed ninth">
                                            <div class="inline green_text">
                                                <span>{{ $all_total['roi']}} %</span>
                                            </div>
                                        </td>
                                        <td class="fixed tenth">
                                            <div class="inline green_text">
                                                <span>￥&thinsp;{{ $all_total['cpa'] }}</span>
                                            </div>
                                        </td>
                                        <td class="fixed eleventh">
                                            <div class="inline">
                                                <span>{{ $all_total['is_num']}} %</span>
                                            </div>
                                        </td>
                                        <td class="fixed twelfth">
                                            <div class="inline">
                                                <span>{{ $all_total['top_part']}} %</span>
                                            </div>
                                        </td>
                                        <td class="fixed thirteenth">
                                            <div class="inline">
                                                <span>{{ $all_total['best_part']}} %</span>
                                            </div>
                                        </td>
                                        <td class="fixed fourteenth">
                                            <div class="inline"> -</div>
                                        </td>
                                        <td class="fixed fifteenth">
                                            <div class="inline"> -</div>
                                        </td>
                                        <td class="fixed sixteenth">
                                            <div class="inline yellow_text">
                                                <span>{{ number_format($all_total['access'])}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed seventeenth">
                                            <div class="inline yellow_text">
                                                <span>{{ $all_total['cvr']}}%</span>
                                            </div>
                                        </td>
                                        <td class="fixed border">
                                            <div style="height: 31px"></div>
                                        </td>
                                        @foreach($code_list as $code)
                                            <td>
                                                <div class="inline left">
                                                    {{isset($code_total[$code->id])?number_format($code_total[$code->id]['confirm_num']):'-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline left">
                                                    {{isset($code_total[$code->id])?number_format($code_total[$code->id]['access']):'-'}}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline yellow_text">
                                                    {{isset($code_total[$code->id])?round($code_total[$code->id]['cvr'],2):'-'}}
                                                    %
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline blue_text left" style="padding: 9px">
                                                    ￥&thinsp;
                                                    <span style="color: #fff">
                                                    {{isset($code_total[$code->id])?number_format($code_total[$code->id]['confirm_price']):'-'}}
                                                    </span>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @if($current_year.'-'.$current_month != date('Y-m'))
                                    <tr class="total confirm">
                                        <td colspan="2" class="date">
                                            <div class="inline">確定数値</div>
                                        </td>
                                        <td class="fixed third">
                                            <div class="inline">
                                                <span>{{$all_adjustments['confirm_num']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed fourth">
                                            <div class="inline blue_text">
                                                <span>￥&thinsp;{{$all_adjustments['confirm_price']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed fifth">
                                            <div class="inline red_text write_allowed">
                                                <span>￥&thinsp;<input name="confirm_total[add_cost]" class="red_text" style="width: 58px"
                                                                      value="{{$all_adjustments['add_cost']}}">
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fixed sixth">
                                            <div class="inline yellow_text">
                                                <span>￥ {{$all_adjustments['profit']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed seventh">
                                            <div class="inline write_allowed">
                                                <span>￥&thinsp;<input name="confirm_total[cpc]"
                                                                      value="{{$all_adjustments['cpc']}}">
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fixed eighth">
                                            <div class="inline write_allowed">
                                                <span>￥&thinsp;<input name="confirm_total[mcpa]"
                                                                      value="{{$all_adjustments['mcpa']}}">
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fixed ninth">
                                            <div class="inline green_text">
                                                <span>{{$all_adjustments['roi']}} %</span>
                                            </div>
                                        </td>
                                        <td class="fixed tenth">
                                            <div class="inline green_text">
                                                <span>￥&thinsp;{{$all_adjustments['cpa']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed eleventh">
                                            <div class="inline write_allowed">
                                                <span><input name="confirm_total[is_num]" value="{{$all_adjustments['is_num']}}">%</span>
                                            </div>
                                        </td>
                                        <td class="fixed twelfth">
                                            <div class="inline write_allowed">
                                                <span><input name="confirm_total[top_part]" value="{{$all_adjustments['top_part']}}">%</span>
                                            </div>
                                        </td>
                                        <td class="fixed thirteenth">
                                            <div class="inline write_allowed">
                                                <span><input name="confirm_total[best_part]" value="{{$all_adjustments['best_part']}}">%</span>
                                            </div>
                                        </td>
                                        <td class="fixed fourteenth">
                                            <div class="inline"> -</div>
                                        </td>
                                        <td class="fixed fifteenth">
                                            <div class="inline"> -</div>
                                        </td>
                                        <td class="fixed sixteenth">
                                            <div class="inline yellow_text">
                                                <span>{{$all_adjustments['access']}}</span>
                                            </div>
                                        </td>
                                        <td class="fixed seventeenth">
                                            <div class="inline yellow_text">
                                                <span>{{$all_adjustments['cvr']}} %</span>
                                            </div>
                                        </td>
                                        <td class="fixed border"><div></div></td>
                                        @foreach($code_list as $code)

                                            <td>
                                                <div class="inline left write_allowed">
                                                    <input name="adjustment[{{$code->id}}][confirm_num]"
                                                           value="{{isset($adjustments[$code->id]->confirm_num)?$adjustments[$code->id]->confirm_num : ''}}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline left write_allowed">
                                                    <input name="adjustment[{{$code->id}}][access]"
                                                           value="{{isset($adjustments[$code->id]->access)?$adjustments[$code->id]->access : ''}}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline yellow_text">
                                                    @if(!empty($adjustments[$code->id]->confirm_num) && !empty($adjustments[$code->id]->access))
                                                        {{round($adjustments[$code->id]->confirm_num / $adjustments[$code->id]->access *100,1)}}%
                                                        @else - @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="inline blue_text left write_allowed">
                                                    ￥&thinsp;<span style="color: #fff">
                                                        <input name="adjustment[{{$code->id}}][confirm_price]" style="width: 56px"
                                                               value="{{isset($adjustments[$code->id]->confirm_price)? number_format($adjustments[$code->id]->confirm_price) : ''}}">
                                                    </span>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endif
                                    </tbody>
                                </table>
                                <input type="hidden" name="year" value="{{ $current_year }}">
                                <input type="hidden" name="month" value="{{ $current_month }}">
                                @if($authority_type != 1)
                                <button type="submit" name="table_update" value="update">表を更新</button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $("tr td").on("click", function (e) {
                $(this).find('input').prop("disabled", false).focus();
            });
            $(".write_allowed").on("click", function (e) {
                $(this).find('textarea').prop("disabled", false).focus();
            });
        });
    </script>
@stop

