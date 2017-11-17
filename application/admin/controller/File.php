<?php 
	namespace app\admin\controller;

	use \base\Base_1;
	use think\Db;
	use think\Controller;
	use think\Session;
	use think\Request;

	class File extends Controller
	{
		public function upload(){
		    // 获取表单上传文件 例如上传了001.jpg
		    $file = request()->file('download');
		    // 移动到框架应用根目录/public/uploads/ 目录下
		    $info = $file->validate(['size'=>150000,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
		    if($info){
		        // 成功上传后 获取上传信息
		        // 输出 jpg
		        $data = array(
		        	'status' => 1,
		        	'info' => '上传成功',
		        	'savepath' => $info->getSaveName(),
		        	'savesuffix' => $info->getExtension(),
		        	'savename' => $info->getFilename()
		        	);
		        echo json_encode($data);
		        // echo $info->getExtension();
		        // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
		        // echo $info->getSaveName();
		        // // 输出 42a79759f284b767dfcb2a0197904287.jpg
		        // echo $info->getFilename(); 
		    }else{
		        // 上传失败获取错误信息
		        $data = array(
		        	'status' => 0,
		        	'info' => '上传失败,原因：'.$file->getError()
		        	);
		        echo json_encode($data);
		    }
		}
	}
?>