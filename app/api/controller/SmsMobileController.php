<?php
/**
 * 手机号控制器
 */

namespace app\api\controller;

use think\response\Json;
use app\api\service\SmsMobileService;
use app\common\validate\SmsMobileValidate;
use app\api\exception\ApiServiceException;

class SmsMobileController extends ApiBaseController
{
    /**
     * 列表
     * @param SmsMobileService $service
     * @return Json
     */
    public function index(SmsMobileService $service): Json
    {
        try {
            $data   = $service->getList($this->param, $this->page, $this->limit);
            $result = [
                'sms_mobile' => $data,
            ];

            return api_success($result);
        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 添加
     * @param SmsMobileValidate $validate
     * @param SmsMobileService $service
     * @return Json
     */
    public function add(SmsMobileValidate $validate, SmsMobileService $service): Json
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
     * @param SmsMobileValidate $validate
     * @param SmsMobileService $service
     * @return Json
     */
    public function info(SmsMobileValidate $validate, SmsMobileService $service): Json
    {
        $check = $validate->scene('api_info')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {

            $result = $service->getDataInfo($this->id);
            return api_success([
                'sms_mobile' => $result,
            ]);

        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 修改
     * @param SmsMobileService $service
     * @param SmsMobileValidate $validate
     * @return Json
     */
    public function edit(SmsMobileService $service, SmsMobileValidate $validate): Json
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
     * @param SmsMobileService $service
     * @param SmsMobileValidate $validate
     * @return Json
     */
    public function del(SmsMobileService $service, SmsMobileValidate $validate): Json
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
