@extends('common.layout')
@section('content')
    <div id="code_wrap">
        <div class="log_list">
            <table>
                <tr>
                    <th style="width: 12%;">登録日時</th>
                    <th style="width: 25%;">メディア名 / プロモーション名</th>
                    <th>IMP</th>
                    <th>アクセス数</th>
                    <th>CTR</th>
                    <th>発生成果数</th>
                    <th>発生成果額</th>
                    <th>確定成果数</th>
                    <th>確定成果額</th>
                    <th>CVR</th>
                    <th>報酬合計</th>
                </tr>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td class="left">{{ $genres[$log->PromotionCode->genre_id]->name ?? '-' }}
                            / {{ $log->promotion->name ?? '-' }}</td>
                        <td class="right">{{ $log->imp }}imp</td>
                        <td class="right">{{ $log->access }}件</td>
                        <td>{{ $log->ctr }}%</td>
                        <td class="right">{{ $log->occur_num }}件</td>
                        <td class="right">{{ $log->occur_price }}円</td>
                        <td class="right">{{ $log->confirm_num }}件</td>
                        <td class="right">{{ $log->confirm_price }}円</td>
                        <td>{{ $log->cvr }}%</td>
                        <td class="right">{{ $log->total }}円</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@stop

