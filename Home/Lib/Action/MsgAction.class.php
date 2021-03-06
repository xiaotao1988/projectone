<?php
/**
 * 头条
 * 
 */
class MsgAction extends Action{
    const PAGESIZE = 20;

    private $msgtypearr = array(
        '0' => 'dynamic',
        '1' => 'top',
        '2' => 'preview',
        '3' => 'award',
        '4' => 'notice',
        '5' => 'jianzhi',
        '6' => 'cixiu',
        '7' => 'zhubian',
        '8' => 'caoliu',
        '9' => 'taoci',
        '10' => 'nimian',
    );

    /**
    *   首页
    */
	public function index(){

        // toutiao 
        $toutiao = M('tb_msg')->where("type=%d",1)->order("id desc")->find();

        //dongtai  
        $dongtailist = M('tb_msg')->where("type=%d",0)->order("id desc")->limit(4)->select();
        foreach($dongtailist as $key => &$val){
            if($key == 0){
                $val['title'] = mb_substr($val['title'],0,18,'utf-8');
                $val['introduction'] = mb_substr($val['introduction'],0,55,'utf-8');
            }else{
                $val['title'] = mb_substr($val['title'],0,30,'utf-8');
            }
        }
        $dongtaiinfo = array_shift($dongtailist);

        //scroll  
        $scrollimglist = M('tb_scroll_img')->where('type = 0')->order("id desc")->limit(8)->select();

        //tongzhi 
        $tongzhilist = M('tb_msg')->where("type=%d",4)->order("id desc")->limit(5)->select();
        foreach($tongzhilist as $key => &$val){
            if($key == 0){
                $val['title'] = mb_substr($val['title'],0,30,'utf-8');
            }else{
                $val['title'] = mb_substr($val['title'],0,30,'utf-8');
            }
        }

        $this->assign('tongzhilist',$tongzhilist);
        $this->assign('scrollimglist',$scrollimglist);
        $this->assign('dongtailist',$dongtailist);
        $this->assign('dongtaiinfo',$dongtaiinfo);
        $this->assign('toutiao',$toutiao);
		$this->display();
	}

    
    public function tongzhilist(){
        $this->getlist(4);
	}

    public function dongtai(){
        $id = $this->_param('id');
        if(empty($id)) $this->error("wrong id");

        $info = M('tb_msg')->where("id=%d",$id)->find();
        $fjlist = array();
        if(!empty($info['fujians'])){
            $fjlist = M('tb_scroll_img')->where('id in (%s)',$info['fujians'])->order("id desc")->limit(10)->select();
        }
        $this->assign("info",$info);
        $this->assign("fjlist",$fjlist);
        $this->display();
    }
    public function detail($type){
        if(empty($type)) $this->error("wrong page");

        $info = M('tb_msg')->where("type=%d",$type)->order("id desc")->find();
        $fjlist = array();
        if(!empty($info['fujians'])){
            $fjlist = M('tb_scroll_img')->where('id in (%s)',$info['fujians'])->order("id desc")->limit(10)->select();
        }
        $this->assign("info",$info);
        $this->assign("fjlist",$fjlist);
        $this->display("dongtai");
    }

    public function getlist($msgtype){
        $msgtype = intval($msgtype);
        $p = (int)$this->_param("p");
        if(empty($msgtype) || $msgtype > 11) $this->error("error");
        $limit = 20;
        list($list,$count) = $this->getListByType($msgtype,$p,$limit);
        $maxpage = (int)($count / $limit);
        $maxpage = ($maxpage <= 1)?1:$maxpage;
        $this->assign("msgtype",$msgtype);
        $this->assign("list",$list);
        $this->assign("maxpage",$maxpage);
		$this->display("dongtailist");
	}
    public function dongtailist(){
        $limit = 20;
        $msgtype = $this->_param("msgtype");
        $p = $this->_param("p");
        $msgtype = empty($msgtype)?0:$msgtype;
        list($list,$count) = $this->getListByType($msgtype,$p,$limit);
        $maxpage = (int)($count / $limit);
        $maxpage = ($maxpage <= 1)?1:$maxpage;
        $this->assign("msgtype",$msgtype);
        $this->assign("list",$list);
        $this->assign("maxpage",$maxpage);
		$this->display("dongtailist");
	}
    public function preview(){
        $marr = array(15,16,17,18,19,20);
        $mtype = $this->_param("type");
        $p = (int)$this->_param("p");
        if(empty($mtype)) $mtype = 15;
        if( !in_array($mtype,$marr)) $this->error("error");
        $limit = 10;
        $offset = $p * $limit ;
        //scroll  
        $count = M('tb_msg')->where("type = %d ",$mtype)->count();
        $maxpage = (int)($count % $limit);
        $maxpage = ($maxpage <= 1)?1:$maxpage;
        $list = M('tb_msg')->where("type = %d ",$mtype)->order("id desc")->limit($offset.",".$limit)->select();
        $this->assign("list",$list);
        $this->assign("type",$mtype);
        $this->assign("maxpage",$maxpage);
		$this->display("zhanlanlist");
	}
    public function previewto(){
        $marr = array(15,16,17,18,19,20);
        $mtype = $this->_param("type");
        if(empty($mtype) || !in_array($mtype,$marr)){
            $this->ajaxReturn("error ","",0);
        }
        $this->ajaxReturn("ok",U('Msg/preview',array('type'=>$mtype)),1);
	}

    public function award(){
        $this->getlist(3);
	}
    public function zhubianlist(){
        $this->detail(7);
	}
    public function caoliulist(){
        $this->detail(8);
	}

    public function taocilist(){
        $this->detail(9);
	}

    public function jianzhilist(){
        $this->detail(5);
	}

    public function cixiulist(){
        $this->detail(6);
	}
    public function nimianlist(){
        $this->detail(10);
	}

    private function getListByType($msgtype =0 ,$p = 0, $limit = 1){
        $model = M('tb_msg');
        $offset = $p * $limit;
        $count = $model->where("type = %d",$msgtype)->count();
        $list = $model->where("type = %d ",$msgtype)->order("id desc")->limit($offset.",".$limit)->select();
        return array($list,$count);
        
    }
	/**
	 * 跳转到添加页面
	 */
	private function add(){
		$id = $this->_param('id');
		$type = "add";
		if(!empty($id)){
			$info = D('Msg')->getMsgInfo($id);
			$type = "edit";
			$this->assign("info",$info);
        }
        $this->assign("type",$type);
		$this->display();
	}
	
	/**
	 * 将数组转换成where 模式字符串
	 */
	private function _change_to_wherestr($arr){
		$pairs = array();
		$symbolarr = array('>','<','>=','<=','=','like');
		foreach($arr as $k=>$v){
			if(is_array($v)){
				if(in_array($v[1],$symbolarr)){
					if($v[1] == 'like'){
						$pairs[] = sprintf(" and `%s` %s '%%%s%%'",$k,$v[1],$v[0]);	
					}else{
						$pairs[] = sprintf(" and `%s` %s '%s'",$k,$v[1],$v[0]);	
					}
				}	
			}else{
				$pairs[] = sprintf(" and `%s`='%s'",$k,$v);
			}
		}
		return join(' ',$pairs);
	}

}
?>
