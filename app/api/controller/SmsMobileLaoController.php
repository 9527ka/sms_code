<?php
/**
 * 老人机号码控制器
 */

namespace app\api\controller;

use think\response\Json;
use app\api\service\SmsMobileLaoService;
use app\common\validate\SmsMobileLaoValidate;
use app\api\exception\ApiServiceException;

class SmsMobileLaoController extends ApiBaseController
{
    /**
     * 列表
     * @param SmsMobileLaoService $service
     * @return Json
     */
    public function index(SmsMobileLaoService $service): Json
    {
        try {
            $data   = $service->getList($this->param, $this->page, $this->limit);
            $result = [
                'sms_mobile_lao' => $data,
            ];

            return api_success($result);
        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 添加
     * @param SmsMobileLaoValidate $validate
     * @param SmsMobileLaoService $service
     * @return Json
     */
    public function add(SmsMobileLaoValidate $validate, SmsMobileLaoService $service): Json
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
     * @param SmsMobileLaoValidate $validate
     * @param SmsMobileLaoService $service
     * @return Json
     */
    public function info(SmsMobileLaoValidate $validate, SmsMobileLaoService $service): Json
    {
        $check = $validate->scene('api_info')->check($this->param);
        if (!$check) {
            return api_error($validate->getError());
        }

        try {

            $result = $service->getDataInfo($this->id);
            return api_success([
                'sms_mobile_lao' => $result,
            ]);

        } catch (ApiServiceException $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 修改
     * @param SmsMobileLaoService $service
     * @param SmsMobileLaoValidate $validate
     * @return Json
     */
    public function edit(SmsMobileLaoService $service, SmsMobileLaoValidate $validate): Json
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
     * @param SmsMobileLaoService $service
     * @param SmsMobileLaoValidate $validate
     * @return Json
     */
    public function del(SmsMobileLaoService $service, SmsMobileLaoValidate $validate): Json
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
