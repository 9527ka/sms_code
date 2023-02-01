<?php
/**
 * 手机号控制器
 */

namespace app\admin\controller;

use Exception;
use think\Request;
use think\db\Query;
use think\response\Json;
use app\common\model\SmsMobile;
use app\common\model\SmsUrl;
use app\common\validate\SmsMobileValidate;

class SmsMobileController extends AdminBaseController
{
    public function list(Request $request, SmsMobile $model): string
    {
        $mobile = $request->param('_keywords');
        $start_time = $request->param('start_time');
        $end_time = $request->param('end_time');
        if(empty($mobile)){
            $param[] = ['a.delete_time','=',0];
        } else {
            $param[] = ['a.mobile','LIKE', "%".$mobile."%"];
        }
        
        $sms_url_id = $request->param('sms_url_id');
        if(!empty($sms_url_id)){
            $param[] = ['a.sms_url_id', '=', $sms_url_id];
        }
        
        $is_get = $request->param('is_get');
        if(!empty($is_get)){
            $param[] = ['a.is_get', '=', $is_get];
        }
        
        $status = $request->param('status');
        if(isset($status) && in_array($status, ["0","1","2"])){
            $param[] = ['a.status', '=', $status];
        }
        
        if(!empty($start_time)){
            if(empty($end_time)){
                // return admin_error('请选择结束时间');
            }
            $param[] = ['a.update_time', 'between',[strtotime($start_time), strtotime($end_time)]];
        }
        $data = SmsMobile::alias('a')
            ->where($param)
            ->field('a.*,b.channel_name,b.receive_url,b.send_url')
            ->join('sms_url b','a.sms_url_id = b.id')
            ->order('a.update_time DESC')
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page'  => 'page',
                'query'     => $request->get(),
            ]);
        foreach ($data as $v){
            echo "COM".$v['com'].','.$v['mobile'].','.$v['remark']."<br>";
        }
        die;
    }
    /**
     * 列表
     * @param Request $request
     * @param SmsMobile $model
     * @return string
     * @throws Exception
     */
    public function index(Request $request, SmsMobile $model): string
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        // $this->admin['admin_list_rows'] = cookie('admin_list_rows') ?? 200;
        // $data  = $model->scope('where', $param)
        //     ->paginate([
        //         'list_rows' => $this->admin['admin_list_rows'],
        //         'var_page'  => 'page',
        //         'query'     => $request->get(),
        //     ]);
        $mobile = $request->param('_keywords');
        $start_time = $request->param('start_time');
        $end_time = $request->param('end_time');
        if(empty($mobile)){
            $param[] = ['a.delete_time','=',0];
        } else {
            $param[] = ['a.mobile','LIKE', "%".$mobile."%"];
        }
        
        $sms_url_id = $request->param('sms_url_id');
        if(!empty($sms_url_id)){
            $param[] = ['a.sms_url_id', '=', $sms_url_id];
        }
        
        $is_get = $request->param('is_get');
        if(!empty($is_get)){
            $param[] = ['a.is_get', '=', $is_get];
        }
        
        $status = $request->param('status');
        if(isset($status) && in_array($status, ["-1","0","1","2"])){
            // $status = $status == "-2" ? 0 : $status;
            $param[] = ['a.status', '=', $status];
        }
        
        //支付时间
        if(!empty($start_time)){
            if(empty($end_time)){
                // return admin_error('请选择结束时间');
            }
            $param[] = ['a.update_time', 'between',[strtotime($start_time), strtotime($end_time)]];
        }
        $data = SmsMobile::alias('a')
            ->where($param)
            ->field('a.*,b.channel_name,b.receive_url,b.send_url')
            ->join('sms_url b','a.sms_url_id = b.id')
            ->order('a.id DESC')
            ->paginate([
                'list_rows' => $this->admin['admin_list_rows'],
                'var_page'  => 'page',
                'query'     => $request->get(),
            ]);
            // echo SmsMobile::getlastsql();die;
        
        $succNum = SmsMobile::alias('a')
            ->where($param)
            ->where('status',1)
            ->field('a.*,b.channel_name,b.receive_url,b.send_url')
            ->join('sms_url b','a.sms_url_id = b.id')
            ->count();
        $errNum = SmsMobile::alias('a')
            ->where($param)
            ->where('status','in','0,2')
            ->field('a.*,b.channel_name,b.receive_url,b.send_url')
            ->join('sms_url b','a.sms_url_id = b.id')
            ->count();
        // echo SmsMobile::getlastsql();die;
        // 关键词，排序等赋值
        
        $this->assign($request->get());
        
        //渠道链接
        $url_list = SmsUrl::order('id DESC')->select();
        // var_dump($data);die;
        $this->assign([
            'data'  => $data,
            'page'  => $data->render(),
            'total' => $data->total(),
            'url_list' => $url_list,
            'status' => $status,
            'succNum' => $succNum,
            'errNum' => $errNum,
        ]);
        return $this->fetch();
    }

    /**
     * 添加
     * @param Request $request
     * @param SmsMobile $model
     * @param SmsMobileValidate $validate
     * @return string|Json
     * @throws Exception
     */
    public function add(Request $request, SmsMobile $model, SmsMobileValidate $validate)
    {
        if ($request->isPost()) {
            $param           = $request->param();
            $validate_result = $validate->scene('admin_add')->check($param);
            if (!$validate_result) {
                return admin_error($validate->getError());
            }
            
            $result = $model::create($param);

            $url = URL_BACK;
            if (isset($param['_create']) && (int)$param['_create'] === 1) {
               $url = URL_RELOAD;
            }
            return $result ? admin_success('添加成功', [], $url) : admin_error();
        }
        //渠道链接
        $url_list = SmsUrl::select();
        $this->assign([
            'is_get_list'=>SmsMobile::IS_GET_LIST,
            'status_list'=>SmsMobile::STATUS_LIST,
            'url_list' => $url_list
        ]);

        return $this->fetch();
    }

    /**
     * 修改
     * @param $id
     * @param Request $request
     * @param SmsMobile $model
     * @param SmsMobileValidate $validate
     * @return string|Json
     * @throws Exception
     */
    public function edit($id, Request $request, SmsMobile $model, SmsMobileValidate $validate)
    {
        $data = $model->findOrEmpty($id);
        if ($request->isPost()) {
            $param = $request->param();
            $check = $validate->scene('admin_edit')->check($param);
            if (!$check) {
                return admin_error($validate->getError());
            }
            
            $result = $data->save($param);
            return $result ? admin_success('修改成功', [], URL_BACK) : admin_error('修改失败');
        }
        $url_list = SmsUrl::select();
        $this->assign([
            'data' => $data,
            'is_get_list'=>SmsMobile::IS_GET_LIST,'status_list'=>SmsMobile::STATUS_LIST,
            'url_list' => $url_list
        ]);

        return $this->fetch('add');
    }

    /**
     * 删除
     * @param mixed $id
     * @param SmsMobile $model
     * @return Json
     */
    public function del($id, SmsMobile $model): Json
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

    /**
     * 导入
     * @param Request $request
     * @return Json
     */
    public function import(Request $request): Json
    {
        $param           = $request->param();
        $field_name_list = ['com','手机号'];
        if (isset($param['action']) && $param['action'] === 'download_example') {
            $this->downloadExample($field_name_list);
        }

        $field_list = ['com','mobile'];
        $result = $this->importData1($param['sms_url_id'], 'file','sms_mobile',$field_list);

        return true === $result ? admin_success('操作成功', [], URL_RELOAD) : admin_error($result);
    }

    /**
     * 导出
     * @param Request $request
     * @param SmsMobile $model
     * @throws Exception
     */
    public function export(Request $request, SmsMobile $model)
    {
        $param = $request->param();
        // $data  = $model->scope('where', $param)->select();
        $map = [];
        if(!empty($param['sms_url_id'])){
            $map[] = ['a.sms_url_id', '=', $param['sms_url_id']];
        }
        if(!empty($param['is_get'])){
            $map[] = ['a.is_get', '=', $param['is_get']];
        }
        if(isset($param['status'])){
            $map[] = ['a.status', '=', $param['status']];
        }
        if(!empty($param['_keywords'])){
            $map[] = ['a.mobile', 'LIKE', "%".$param['_keywords']."%"];
        }
        
        $data = SmsMobile::alias('a')
            ->where($map)
            ->field('a.*,b.channel_name')
            ->join('sms_url b','a.sms_url_id = b.id')
            ->order('a.update_time DESC')->select();
//  echo SmsMobile::getlastsql();die;
        $header = ['ID','渠道名称','com','手机号','取号状态','状态','验证码','备注','更新时间'];
        $body   = [];
        foreach ($data as $item) {
            $record                = [];
            $record['id'] = $item->id;
$record['channel_name'] = $item->channel_name;
$record['com'] = $item->com;
$record['mobile'] = $item->mobile;
$record['is_get'] = $item->is_get_text;
$record['status'] = $item->status_text;
$record['code'] = $item->code;
$record['remark'] = $item->remark;
$record['update_time'] = $item->update_time;

            $body[] = $record;
        }
        $this->exportData($header, $body, '手机号数据-' . date('YmdHis'));
    }

}
