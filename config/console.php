<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
use app\command\GenerateAppKey;
use app\command\GenerateJwtKey;
use app\command\InitEnv;
use app\command\ResetAdminPassword;
use app\command\GetCodeSave;
use app\command\GetCodeLrj;
use app\command\GetMobileLrj;
use app\command\GetMobileLrjTest;
use app\command\WuxianCode;

return [
    // 指令定义
    'commands' => [
        // 测试指令
        'test'                 => \app\command\Test::class,
        // 初始化env文件
        'init:env'             => InitEnv::class,
        // 生成新的app_key
        'generate:app_key'     => GenerateAppKey::class,
        // 生成新的jwt_key
        'generate:jwt_key'     => GenerateJwtKey::class,
        // 重置后台管理员密码
        'reset:admin_password' => ResetAdminPassword::class,
        //扫描验证码接口，存库
        'getCodeSave'     => GetCodeSave::class,
        //老人机 - 扫描验证码接口，存库
        'getCodeLrj'     => GetCodeLrj::class,
        //老人机 - 获取号码接口，存库
        'getMobileLrj'     => GetMobileLrj::class,
        //老人机 - 获取号码 一秒一个，测试
        // 'getMobileLrjTest' => GetMobileLrjTest::class,
        //扒网页的 抖音取码
        'wuxianCode' => WuxianCode::class,
    ],
];
