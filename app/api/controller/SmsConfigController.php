<?php
/**
 * 配置项控制器
 */

namespace app\api\controller;

use think\response\Json;
use app\api\service\SmsConfigService;
use app\common\validate\SmsConfigValidate;
use app\api\exception\ApiServiceException;

class SmsConfigController extends ApiBaseController
{
    /**
     * 列表
     * @param SmsConfigService $service
     * @return Json
     */
    public function index(SmsConfigService $service): Json
    {
        try {
            $data   = $service->getList($this->param, $this->page, $this->limit);
            $result = [
                'sms_config' => $data,
            ];

            return api_success($result);
        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    

    /**
     * 详情
     * @param SmsConfigValidate $validate
     * @param SmsConfigService $service
     * @return Json
     */
    public function info(SmsConfigValidate $validate, SmsConfigService $service): Json
    {
        $check = $validate->scene('api_info')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {

            $result = $service->getDataInfo($this->id);
            return api_success([
                'sms_config' => $result,
            ]);

        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 修改
     * @param SmsConfigService $service
     * @param SmsConfigValidate $validate
     * @return Json
     */
    public function edit(SmsConfigService $service, SmsConfigValidate $validate): Json
    {
        $check = $validate->scene('api_edit')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {
            $service->updateData($this->id, $this->param);
            return api_success();
        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 删除
     * @param SmsConfigService $service
     * @param SmsConfigValidate $validate
     * @return Json
     */
    public function del(SmsConfigService $service, SmsConfigValidate $validate): Json
    {
        $check = $validate->scene('api_del')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {
            $service->deleteData($this->id);
            return api_success();
        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    

    
}
