<?php
return [
    'error' => '错误',
    'description' => '描述',
    'resolution' => '解决方案',

    'ERR001' => [
        'message' => '请先选择柜台！',
        'description' => '用户在未选择柜台的情况下尝试执行操作。',
        'resolution' => '提示用户先选择柜台后再继续。',
    ],
    'ERR002' => [
        'message' => '无通话',
        'description' => '当前无可执行操作的通话。',
        'resolution' => '启动或等待新通话。',
    ],
    'ERR003' => [
        'message' => '请先关闭当前服务中的通话！',
        'description' => '用户必须先结束当前通话才能开始新通话。',
        'resolution' => '请先关闭当前通话再继续。',
    ],
    'ERR004' => [
        'message' => '该通话已被暂时挂起',
        'description' => '所选通话被暂时挂起。',
        'resolution' => '请恢复通话后再继续。',
    ],
    'ERR005' => [
        'message' => '该通话已被暂时挂起（开始）',
        'description' => '排队的通话进入了临时挂起状态。',
        'resolution' => '请等待挂起解除或手动恢复。',
    ],
    'ERR006' => [
        'message' => '排队号码已存在',
        'description' => '系统中已存在该排队号码。',
        'resolution' => '请生成新的唯一排队号码。',
    ],
    'ERR007' => [
        'message' => '系统中没有您正在服务的通话！',
        'description' => '没有找到分配给该用户的活跃通话。',
        'resolution' => '请确保当前有正在服务的通话。',
    ],
    'ERR008' => [
        'message' => '排队号码已存在',
        'description' => '数据库中排队号码已存在',
        'resolution' => '请检查您的排队号码',
    ],
    'ERR009' => [
        'message' => '当前未关闭的排队已被重置！',
        'description' => '当前排队已被重置',
        'resolution' => '您的排队已被重置',
    ],

    'BOOK001' => [
        'message' => '由于规则无效，无法生成票据。',
        'description' => '系统配置阻止了票据的创建。',
        'resolution' => '请联系管理员检查预约规则。',
    ],
    'BOOK002' => [
        'message' => '支付失败：出现问题',
        'description' => '支付过程中出现未知问题。',
        'resolution' => '请重试支付或联系支持。',
    ],
    'BOOK003' => [
        'message' => '缺少支付服务密钥',
        'description' => '未设置支付 API 凭证。',
        'resolution' => '请在设置中配置 API Key 和 Secret。',
    ],
    'BOOK004' => [
        'message' => '支付设置未配置',
        'description' => '支付设置尚未完成。',
        'resolution' => '请在管理员面板中完成支付设置。',
    ],

    'SUCCESS001' => [
        'message' => '呼叫成功',
    ],
    'SUCCESS002' => [
        'message' => '暂停处理成功并已发送通知',
    ],
    'SUCCESS003' => [
        'message' => '呼叫开始成功'
    ],
    'SUCCESS004' => [
        'message' => '呼叫成功结束'
    ],
    'SUCCESS005' => [
        'message' => '呼叫转接成功'
    ],
    'SUCCESS006' => [
        'message' => '重新呼叫成功'
    ],
    'SUCCESS007' => [
        'message' => '呼叫返回成功'
    ],
    'SUCCESS008' => [
        'message' => '请求已发送至管理员'
    ],
    'SUCCESS009' => [
        'message' => '挂起成功'
    ],
    'SUCCESS0010' => [
        'message' => '取消成功'
    ],
    'SUCCESS0011' => [
        'message' => '短信发送成功！'
    ],
    'SUCCESS0012' => [
        'message' => '排队生成成功！'
    ],
    'SUCCESS0013' => [
        'message' => '预估备注更新成功！'
    ],
    'SUCCESS0014' => [
        'message' => '呼叫回退成功'
    ],
    'SUCCESS0015' => [
        'message' => '访客信息编辑成功'
    ],
    'SUCCESS0016' => [
        'message' => '未接通话处理成功'
    ],
    'SUCCESS0017' => [
    'message' => '数据保存成功'
],


    'VAL001' => [
        'message' => '请输入排队号码和分类',
    ],
    'VAL002' => [
        'message' => '请输入休息类型和备注',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => '点击“继续”按钮以解锁屏幕！休息时间为',
    'minutes.' => '分钟。',
    'CONTINUE' => '继续',
    'Call started Successfully' => '呼叫开始成功',
    'success' => '成功',
    'Suspension processed successfully with notifications sent' => '暂停处理成功并已发送通知',
    'Are you sure' => '您确定吗',
    'warning' => '警告',
    'You want to revert this' => '您想要回退此操作',
    'YES, REVERT IT' => '是的，回退',
    'No, CANCEL' => '不，取消',
    'Please rate our service' => '请为我们的服务评分',
    'Excellent' => '非常好',
    'Good' => '好',
    'Neutral' => '一般',
    'Poor' => '差',
    'Please Wait' => '请稍等',
    'Revert Queue' => '回退排队',
    'Cancelled' => '已取消',
    'Your data is safe' => '您的数据是安全的',
    'error' => '错误',
    "You won't be able to revert this" => '此操作无法撤销',
    'OK' => '确定',
    'Cancel' => '取消',
    'Please enter queue number and category' => '请输入排队号码和分类',
    'Break' => '休息',
    'Choose Any Reason' => '选择任意原因',
    'Comment' => '备注',
    'Please enter break type and comment' => '请输入休息类型和备注',
    'Enter Queue Number' => '输入排队号码',
    'Select Category' => '选择分类',
    'Type of Break' => '休息类型',
    'Unlock Screen' => '解锁屏幕',
    'Updating' => '正在更新',
    'Success!' => '成功！',
    'Yes, delete it' => '是的，删除它',
    'Data Deleted Successfully' => '数据删除成功',
    'No record selected' => '未选择记录',

];
