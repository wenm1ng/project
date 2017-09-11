<?php
namespace app\api\controller;
use \base\Baseapi;

class Upload extends Baseapi
{
    /**
     * 添加店铺
     */
    public function images(){
        //判断临时文件存放路径是否包含用户上传的文件
        $filetype = '';
        if(isset($_POST['filetype'])) $filetype = $_POST['filetype'];
        if(isset($_FILES["file"]) && is_uploaded_file($_FILES["file"]["tmp_name"])){
            //为了更高效，将信息存放在变量中
            $upfile = $_FILES["file"];//用一个数组类型的字符串存放上传文件的信息
            $name = $upfile["name"];//便于以后转移文件时命名
            $type = $upfile["type"];//上传文件的类型
            $size = $upfile["size"];//上传文件的大小
            $tmp_name = $upfile["tmp_name"];//用户上传文件的临时名称
            $error = $upfile["error"];//上传过程中的错误信息
            if($error != '0'){
                return json($this->erres("图片上传出错"));
            }
            //echo $name;
            //对文件类型进行判断，判断是否要转移文件,如果符合要求则设置$ok=1即可以转移
            if(!in_array($type, array("image/jpg", "image/jpeg", "image/gif", "image/png"))){
                return json($this->erres("上传图片格式错误"));
            }
            $filename = $this->random().$name;
            $dirurl = 'shopicon/';
            if($filetype == 'dishicon') $dirurl = 'dishicon/';
            //调用move_uploaded_file（）函数，进行文件转移
            move_uploaded_file($tmp_name, UPLOAD_PATH.$dirurl.$filename);
            //$this->compressed_image(UPLOAD_PATH.'shopicon/'.$filename, UPLOAD_PATH.'shopicon/min_'.$filename);
            //操作成功后，提示成功
            return json($this->sucjson(array("imgpath" => "/upload/".$dirurl.$filename)));
        }
    }
    
    //生成随机文件名函数 
    public function random($length = 10) 
    { 
        $hash = 'CR-'; 
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'; 
        $max = strlen($chars) - 1; 
        mt_srand((double)microtime() * 1000000); 
        for($i = 0; $i < $length; $i++) 
        { 
            $hash .= $chars[mt_rand(0, $max)]; 
        } 
        return $hash; 
    } 
    /**
    * desription 判断是否gif动画
    * @param sting $image_file图片路径
    * @return boolean t 是 f 否
    */
    public function check_gifcartoon($image_file){
        $fp = fopen($image_file,'rb');
        $image_head = fread($fp,1024);
        fclose($fp);
        return preg_match("/".chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0'."/",$image_head)?false:true;
    }
    
    /**
      * desription 压缩图片
      * @param sting $imgsrc 图片路径
      * @param string $imgdst 压缩后保存路径
      */
    public function compressed_image($imgsrc, $imgdst){
        list($width,$height,$type)=getimagesize($imgsrc);
        $new_width = ($width>600?600:$width)*0.9;
        $new_height =($height>600?600:$height)*0.9;
        switch($type){
            case 1:
                $giftype=$this->check_gifcartoon($imgsrc);
                if($giftype){
                    header('Content-Type:image/gif');
                    $image_wp=imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromgif($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    //75代表的是质量、压缩图片容量大小
                    imagejpeg($image_wp, $imgdst,75);
                    imagedestroy($image_wp);
                }
                break;
            case 2:
                header('Content-Type:image/jpeg');
                $image_wp=imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromjpeg($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //75代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst,75);
                imagedestroy($image_wp);
                break;
            case 3:
                header('Content-Type:image/png');
                $image_wp=imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //75代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst,75);
                imagedestroy($image_wp);
                break;
        }
    }
}