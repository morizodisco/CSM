//入力ログ格納 (/report/detail)
// $(function() {
//     $('.code_total').on('keyup', function(){
//         const self = $(this);
//
//         $.ajax({
//             url: '/ajax/code_total',
//             type: 'POST',
//             data: {'id': self.data('id'),
//                 'year': self.attr('data-year'),
//                 'month': self.attr('data-month'),
//                 'date': self.attr('data-date'),
//                 'time': self.attr('data-time'),
//                 'genre_id': self.attr('data-genre_id'),
//                 'name': self.attr('name'),
//                 'num': self.val(),
//             },
//             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
//         })
//             .done(function (data) {
//                 //console.log(data);
//             })
//             .fail(function (data) {
//                 console.log('error');
//             });
//     });
// });
//
// //入力ノート格納 (/report/detail)
// $(function() {
//     $('.note_total').on('keyup', function(){
//         const self = $(this);
//
//         $.ajax({
//             url: '/ajax/note_total',
//             type: 'POST',
//             data: {'id': self.data('id'),
//                 'genre_id': self.data('genre_id'),
//                 'name': self.attr('name'),
//                 'date': self.data('date'),
//                 'note': self.val(),
//             },
//             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
//         })
//             .done(function (data) {
//                 //console.log(data);
//             })
//             .fail(function (data) {
//                 console.log('error');
//             });
//     });
// });

//コード別入力ログ格納 (/report/detail)
// $(function() {
//     $('.code_item').on('keyup', function(){
//         const self = $(this);
//
//         $.ajax({
//             url: '/ajax/code_item',
//             type: 'POST',
//             data: {
//                 'code_id': self.attr('data-code_id'),
//                 'year': self.attr('data-year'),
//                 'month': self.attr('data-month'),
//                 'date': self.attr('data-date'),
//                 'time': self.attr('data-time'),
//                 'name': self.attr('name'),
//                 'num': self.val(),
//             },
//             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
//         })
//             .done(function (data) {
//                 console.log(data);
//             })
//             .fail(function (data) {
//                 console.log('error');
//             });
//     });
// });

//入力ノート格納 (/report)
$(function() {
    $('.genre_note').on('keyup', function(){
        const self = $(this);

        $.ajax({
            url: '/ajax/genre_note',
            type: 'POST',
            data: {
                'genre_id': self.attr('data-genre_id'),
                'note': self.val(),
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        })
            .done(function (data) {
                //console.log(data);
            })
            .fail(function (data) {
                console.log('error');
            });
    });
});

//入力議事録格納 (/report/detail)
$(function() {
    $('.report_minutes').on('keyup', function(){
        const self = $(this);

        $.ajax({
            url: '/ajax/report_minutes',
            type: 'POST',
            data: {
                'report_id': self.attr('data-report_id'),
                'minutes': self.val(),
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        })
            .done(function (data) {
                //console.log(data);
            })
            .fail(function (data) {
                console.log('error');
            });
    });
});
