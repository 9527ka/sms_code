<?php
/**
 * 配置项控制器
 */

namespace app\admin\controller;

use Exception;
use think\Request;
use think\db\Query;
use think\response\Json;
use app\common\model\SmsConfig;

use app\common\validate\SmsConfigValidate;

class SmsConfigController extends AdminBaseController
{

    /**
     * 列表
     * @param Request $request
     * @param SmsConfig $model
     * @return string
     * @throws Exception
     */
    public function index(Request $request, SmsConfig $model): string
    {
        $param = $request->param();
        $data  = $model->scope('where', $param)
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page'  => 'page',
                'query'     => $request->get(),
            ]);
        // 关键词，排序等赋值
        $this->assign($request->get());

        $this->assign([
            'data'  => $data,
            'page'  => $data->render(),
            'total' => $data->total(),
            
        ]);
        return $this->fetch();
    }



    /**
     * 修改
     * @param $id
     * @param Request $request
     * @param SmsConfig $model
     * @param SmsConfigValidate $validate
     * @return string|Json
     * @throws Exception
     */
    public function edit(Request $request, SmsConfig $model, SmsConfigValidate $validate)
    {
        $data = $model->findOrEmpty();
        $param = $request->param();
        if ($request->isPost()) {
            $param = $request->param();
            $result = smsConfig::where('id',$param['id'])->update(['value'=>$param['value']]);
            return $result ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }
        $data  = $model->select();
        // var_dump($data);die;
        $this->assign([
            'data' => $data,
        ]);

        return $this->fetch('add');
    }

    /**
     * 删除
     * @param mixed $id
     * @param SmsConfig $model
     * @return Json
     */
    public function del($id, SmsConfig $model): Json
    {
        $check = $model->inNoDeletionIds($id);
        if (false !== $check) {
            return admin_error('ID为' . $check . '的数据不能被删除');
        }

        $result = $model::destroy(static function ($query) use ($id) {
            /** @var Query $query */
            $query->whereIn('id', $id);
        });

        return $result ? admin_success('删除成功', [], URL_RELOAD) : admin_error('删除失败');
    }









}
