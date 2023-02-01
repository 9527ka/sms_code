<?php
/**
 * 渠道链接控制器
 */

namespace app\api\controller;

use think\response\Json;
use app\api\service\SmsUrlService;
use app\common\validate\SmsUrlValidate;
use app\api\exception\ApiServiceException;

class SmsUrlController extends ApiBaseController
{
    /**
     * 列表
     * @param SmsUrlService $service
     * @return Json
     */
    public function index(SmsUrlService $service): Json
    {
        try {
            $data   = $service->getList($this->param, $this->page, $this->limit);
            $result = [
                'sms_url' => $data,
            ];

            return api_success($result);
        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 添加
     * @param SmsUrlValidate $validate
     * @param SmsUrlService $service
     * @return Json
     */
    public function add(SmsUrlValidate $validate, SmsUrlService $service): Json
    {
        $check = $validate->scene('api_add')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        $result = $service->createData($this->param);

        return $result ? api_success() : api_error();
    }

    /**
     * 详情
     * @param SmsUrlValidate $validate
     * @param SmsUrlService $service
     * @return Json
     */
    public function info(SmsUrlValidate $validate, SmsUrlService $service): Json
    {
        $check = $validate->scene('api_info')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {

            $result = $service->getDataInfo($this->id);
            return api_success([
                'sms_url' => $result,
            ]);

        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 修改
     * @param SmsUrlService $service
     * @param SmsUrlValidate $validate
     * @return Json
     */
    public function edit(SmsUrlService $service, SmsUrlValidate $validate): Json
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
     * @param SmsUrlService $service
     * @param SmsUrlValidate $validate
     * @return Json
     */
    public function del(SmsUrlService $service, SmsUrlValidate $validate): Json
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
