<?php
return [
    'error' => 'エラー',
    'description' => '説明',
    'resolution' => '解決方法',

    'ERR001' => [
        'message' => '最初にカウンターを選択してください！',
        'description' => 'ユーザーがカウンターを選択せずに操作を試みました。',
        'resolution' => '先にカウンターを選択するようユーザーに促してください。',
    ],
    'ERR002' => [
        'message' => '呼び出しがありません',
        'description' => '現在の呼び出しがないため操作できません。',
        'resolution' => '新しい呼び出しを開始するか、呼び出しを待ってください。',
    ],
    'ERR003' => [
        'message' => '最初に現在の呼び出しを終了してください！',
        'description' => '新しい呼び出しを始める前にアクティブな呼び出しを終了する必要があります。',
        'resolution' => '現在の呼び出しを終了してから続行してください。',
    ],
    'ERR004' => [
        'message' => 'この呼び出しは一時的に保留中です',
        'description' => '選択した呼び出しは一時的に保留状態です。',
        'resolution' => '呼び出しを再開してから続行してください。',
    ],
    'ERR005' => [
        'message' => 'この呼び出しは一時的に保留中です（開始）',
        'description' => 'キューにある呼び出しが一時的に保留になりました。',
        'resolution' => '保留が解除されるまで待つか、手動で再開してください。',
    ],
    'ERR006' => [
        'message' => 'このキュー番号は既に存在します',
        'description' => 'キュー番号はすでにシステムに存在しています。',
        'resolution' => '新しいユニークなキュー番号を生成してください。',
    ],
    'ERR007' => [
        'message' => '現在処理中の呼び出しがシステムにありません！',
        'description' => 'ユーザーに割り当てられたアクティブな呼び出しが見つかりません。',
        'resolution' => '現在呼び出し中であることを確認してください。',
    ],
    'ERR008' => [
        'message' => 'このキュー番号は既に存在します',
        'description' => 'このキュー番号はデータベースに既に存在します。',
        'resolution' => 'キュー番号を確認してください。',
    ],
    'ERR009' => [
        'message' => '未終了のキューがリセットされました！',
        'description' => '現在のキューがリセットされました。',
        'resolution' => 'キューがリセットされました。',
    ],

    'BOOK001' => [
        'message' => '無効なルールのためチケットを発行できません。',
        'description' => 'システム設定によりチケット作成がブロックされました。',
        'resolution' => '管理者に連絡して予約ルールを確認してください。',
    ],
    'BOOK002' => [
        'message' => '支払い失敗: エラーが発生しました',
        'description' => '支払い処理中に不明な問題が発生しました。',
        'resolution' => '再度お支払いを試すか、サポートへ連絡してください。',
    ],
    'BOOK003' => [
        'message' => '支払いサービスキーがありません',
        'description' => '支払いAPI認証情報が設定されていません。',
        'resolution' => '設定画面でAPIキーとシークレットを設定してください。',
    ],
    'BOOK004' => [
        'message' => '支払い設定が未構成です',
        'description' => '支払い設定が不完全です。',
        'resolution' => '管理画面で支払い設定を完了してください。',
    ],

    'SUCCESS001' => [
        'message' => '呼び出しが成功しました',
    ],
    'SUCCESS002' => [
        'message' => '停止処理が成功し、通知が送信されました',
    ],
    'SUCCESS003' => [
        'message' => '呼び出し開始に成功しました',
    ],
    'SUCCESS004' => [
        'message' => '呼び出し終了に成功しました',
    ],
    'SUCCESS005' => [
        'message' => '呼び出し転送が成功しました',
    ],
    'SUCCESS006' => [
        'message' => '再呼び出しに成功しました',
    ],
    'SUCCESS007' => [
        'message' => '呼び出しの戻し処理が成功しました',
    ],
    'SUCCESS008' => [
        'message' => '管理者へリクエストが送信されました',
    ],
    'SUCCESS009' => [
        'message' => '保留に成功しました',
    ],
    'SUCCESS0010' => [
        'message' => 'キャンセルが成功しました',
    ],
    'SUCCESS0011' => [
        'message' => 'SMSが送信されました！',
    ],
    'SUCCESS0012' => [
        'message' => 'キューが正常に生成されました！',
    ],
    'SUCCESS0013' => [
        'message' => '見積メモが更新されました！',
    ],
    'SUCCESS0014' => [
        'message' => '呼び出しの復元が成功しました',
    ],
    'SUCCESS0015' => [
        'message' => '訪問者情報を正常に編集しました',
    ],
    'SUCCESS0016' => [
        'message' => '不在呼び出しを記録しました',
    ],

    'VAL001' => [
        'message' => 'キュー番号とカテゴリーを入力してください',
    ],
    'VAL002' => [
        'message' => '休憩の種類とコメントを入力してください',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => '「続行」ボタンをクリックして画面を解除してください。休憩時間は',
    'minutes.' => '分です。',
    'CONTINUE' => '続行',
    'Call started Successfully' => '呼び出し開始に成功しました',
    'success' => '成功',
    'Suspension processed successfully with notifications sent' => '停止処理が成功し、通知が送信されました',
    'Are you sure' => '本当に実行しますか？',
    'warning' => '警告',
    'You want to revert this' => 'この操作を元に戻しますか？',
    'YES, REVERT IT' => 'はい、元に戻します',
    'No, CANCEL' => 'いいえ、キャンセル',
    'Please rate our service' => 'サービスの評価をお願いします',
    'Excellent' => 'とても良い',
    'Good' => '良い',
    'Neutral' => '普通',
    'Poor' => '悪い',
    'Please Wait' => 'お待ちください',
    'Revert Queue' => 'キューを元に戻す',
    'Cancelled' => 'キャンセル済み',
    'Your data is safe' => 'データは安全です',
    'error' => 'エラー',
    "You won't be able to revert this" => 'この操作は元に戻せません',
    'OK' => 'OK',
    'Cancel' => 'キャンセル',
    'Please enter queue number and category' => 'キュー番号とカテゴリーを入力してください',
    'Break' => '休憩',
    'Choose Any Reason' => '理由を選択してください',
    'Comment' => 'コメント',
    'Please enter break type and comment' => '休憩の種類とコメントを入力してください',
    'Enter Queue Number' => 'キュー番号を入力',
    'Select Category' => 'カテゴリーを選択',
    'Type of Break' => '休憩の種類',
    'Unlock Screen' => '画面を解除',
    'Updating' => '更新中',
];
