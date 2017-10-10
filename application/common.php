<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 验证手机号码正确性
 * @param $mobile
 * @return bool
 */
function check_mobile($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

//日志
function error_lo($name,$content){
    file_put_contents('../log/'.$name.'.txt', $content.'------------------------------------'.date('Y-m-d H:i:s')."\r\n",FILE_APPEND);
}

/**
 * 检测身份证号码是否合法
 * @param $idcode
 * @return bool
 */
function check_idcode($idcode)
{
    if (preg_match('/^[0-9a-zA-Z]{15,18}$/D', $idcode)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测时间格式
 * @param $datetime
 * @param string $formate
 * @return bool
 */
function check_datetime($datetime, $formate = 'yyyy-mm-dd hh:ii:ss')
{
    $matchstr = '/^'.$formate.'$/s';
    $matchstr = preg_replace('/yyyy/i', '\d{4}', $matchstr);
    $matchstr = preg_replace('/mm|dd|hh|ii|ss/i', '\d{2}', $matchstr);
    if (preg_match($matchstr, $datetime)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测用户名格式
 * @param $str
 * @return bool
 */
function checkUserName($str)
{
    if (strlen($str) == 0 || is_null($str))
    {
        return false;
    }
    //输入的数据必须是英文和数字
    $pattern = "/^([A-Z|a-z|0-9])+$/";
    if (! preg_match($pattern, $str)){
        return false;
    }
    return true;
}

function is_login(){
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == data_auth_sign($user) ? $user : 0;
    }
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator(){
    $uid = session('is_admin') == 1 ? is_login() : 1;
    return $uid ;
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($user) {
    // //数据类型检测
    // if(!is_array($data)){
    //     $data = (array)$data;
    // }
    // ksort($data); //排序
    // $code = http_build_query($data); //url编码并生成query字符串
    // $sign = sha1($code); //生成签名
    // return $sign;

     //写登录信息
        $ck = 'ck_' . strtoupper(base64_encode(md5($user.'wenminghenshuai')));
        return $ck;
}

function exportExcel($width_arr,$excel_content, $excel_file, 
    $excel_props = array(
        'creator' => 'WWSP Tool', 
        'title' => 'WWSP_Tracking EXPORT EXCEL' , 
        'subject' => 'WWSP_Tracking EXPORT EXCEL', 
        'desc' => 'WWSP_Tracking EXPORT EXCEL', 
        'keywords' => 'WWSP Tool Generated Excel, Author: Scott Huang', 
        'category' => 'WWSP_Tracking EXPORT EXCEL'
        )
    )
{
    if (!is_array($excel_content)) {
        return FALSE;
    }

    if (empty($excel_file)) {
        return FALSE;
    }
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new PHPExcel();
    $objProps = $objPHPExcel->getProperties();
    $objProps->setCreator($excel_props['creator']);
    $objProps->setLastModifiedBy($excel_props['creator']);
    $objProps->setTitle($excel_props['title']);
    $objProps->setSubject($excel_props['subject']);
    $objProps->setDescription($excel_props['desc']);
    $objProps->setKeywords($excel_props['keywords']);
    $objProps->setCategory($excel_props['category']);

    $style_obj = new PHPExcel_Style();
    $style_array = array(
       'borders' => array(
           // 'top' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
           // 'left' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
           // 'bottom' => array('style' => \PHPExcel_Style_Border::BORDER_THIN),
           // 'right' => array('style' => \PHPExcel_Style_Border::BORDER_THIN)
       ),
        'alignment' => array(
            'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            'wrap' => true
        ),
    );
    $style_obj->applyFromArray($style_array);

//开始执行EXCEL数据导出
    //start export excel
    for ($i = 0; $i < count($excel_content); $i++) {
        $each_sheet_content = $excel_content[$i];
        if ($i == 0) {
//默认会创建一个sheet页，故不需在创建
            //There will be a default sheet, so no need create
            $objPHPExcel->setActiveSheetIndex(intval(0));
            $current_sheet = $objPHPExcel->getActiveSheet();
        } else {
//创建sheet
            //create sheet
            $objPHPExcel->createSheet();
            $current_sheet = $objPHPExcel->getSheet($i);
        }
//设置sheet title
        //set title
        $current_sheet->setTitle(str_replace(array('/', '*', '?', '\\', ':', '[', ']'), array('_', '_', '_', '_', '_', '_', '_'), substr($each_sheet_content['sheet_name'], 0, 30))); //add by Scott
        // $current_sheet->getColumnDimension()->setAutoSize(true); //Scott, set column autosize
//设置sheet当前页的标题
        //set sheet's current title
        $_columnIndex = 'A';

        $lineRange = "A1:" . excelColumnName(count($each_sheet_content['sheet_title'])) . "1";
        $current_sheet->setSharedStyle($style_obj, $lineRange);

        if (array_key_exists('sheet_title', $each_sheet_content) && !empty($each_sheet_content['sheet_title'])) {
            //header color
            if (array_key_exists('headerColor', $each_sheet_content) && is_array($each_sheet_content['headerColor']) and !empty($each_sheet_content['headerColor'])) {
                if (isset($each_sheet_content['headerColor']["color"]) and $each_sheet_content['headerColor']['color'])
                    $current_sheet->getStyle($lineRange)->getFont()->getColor()->setARGB($each_sheet_content['headerColor']['color']);
                //background
                if (isset($each_sheet_content['headerColor']["background"]) and $each_sheet_content['headerColor']['background']) {
                    $current_sheet->getStyle($lineRange)->getFill()->getStartColor()->setRGB($each_sheet_content['headerColor']["background"]);
                    $current_sheet->getStyle($lineRange)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                }
            }

            for ($j = 0; $j < count($each_sheet_content['sheet_title']); $j++) {
                $current_sheet->setCellValueByColumnAndRow($j, 1, $each_sheet_content['sheet_title'][$j]);
                //start handle hearder column css
                if (array_key_exists('headerColumnCssClass', $each_sheet_content)) {
                    if (isset($each_sheet_content["headerColumnCssClass"][$each_sheet_content['sheet_title'][$j]])) {
                        $tempStyle = $each_sheet_content["headerColumnCssClass"][$each_sheet_content['sheet_title'][$j]];
                        $tempColumn = excelColumnName($j + 1) . "1";
                        if (isset($tempStyle["color"]) and $tempStyle['color'])
                            $current_sheet->getStyle($tempColumn)->getFont()->getColor()->setARGB($tempStyle['color']);
                        //background
                        if (isset($tempStyle["background"]) and $tempStyle['background']) {
                            $current_sheet->getStyle($tempColumn)->getFill()->getStartColor()->setRGB($tempStyle["background"]);
                            $current_sheet->getStyle($tempColumn)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        }
                    }
                }
                if (!empty($width_arr)) {
                    $current_sheet->getColumnDimension($_columnIndex)->setWidth($width_arr[$i][$j]);
                }
                
                // $current_sheet->getColumnDimension($_columnIndex)->setAutoSize(true); //
                
                $_columnIndex++;
            }
        }
        if (array_key_exists('freezePane', $each_sheet_content) && !empty($each_sheet_content['freezePane'])) {
            $current_sheet->freezePane($each_sheet_content['freezePane']);
        }
//写入sheet页面内容
        //write sheet content
        if (array_key_exists('ceils', $each_sheet_content) && !empty($each_sheet_content['ceils'])) {
            for ($row = 0; $row < count($each_sheet_content['ceils']); $row++) {
                //setting row css
                $lineRange = "A" . ($row + 2) . ":" . excelColumnName(count($each_sheet_content['ceils'][$row])) . ($row + 2);
                if (($row + 1) % 2 == 1 and isset($each_sheet_content["oddCssClass"])) {
                    if ($each_sheet_content["oddCssClass"]["color"])
                        $current_sheet->getStyle($lineRange)->getFont()->getColor()->setARGB($each_sheet_content["oddCssClass"]["color"]);
                    //background
                    if ($each_sheet_content["oddCssClass"]["background"]) {
                        $current_sheet->getStyle($lineRange)->getFill()->getStartColor()->setRGB($each_sheet_content["oddCssClass"]["background"]);
                        $current_sheet->getStyle($lineRange)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    }
                } else if (($row + 1) % 2 == 0 and isset($each_sheet_content["evenCssClass"])) {
//                        echo "even",$row,"<BR>";
                    if ($each_sheet_content["evenCssClass"]["color"])
                        $current_sheet->getStyle($lineRange)->getFont()->getColor()->setARGB($each_sheet_content["evenCssClass"]["color"]);
                    //background
                    if ($each_sheet_content["evenCssClass"]["background"]) {
                        $current_sheet->getStyle($lineRange)->getFill()->getStartColor()->setRGB($each_sheet_content["evenCssClass"]["background"]);
                        $current_sheet->getStyle($lineRange)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    }
                }
                //write content
                for ($l = 0; $l < count($each_sheet_content['ceils'][$row]); $l++) {
                    $current_sheet->setCellValueByColumnAndRow($l, $row + 2, $each_sheet_content['ceils'][$row][$l]);
                }
            }
        }

    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
   
    $file_name = $excel_file . '-' . date('Ymd');
error_reporting(E_ALL);
    ob_end_clean();//清除缓冲区,避免乱码
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
    header('Cache-Control: max-age=0');

    $objWriter->save('php://output');
   
}

function getCssClass($code = '')
{
    $cssClass =
        array(
            'red' => array('color' => 'FFFFFF', 'background' => 'FF0000'),
            'pink' => array('color' => '', 'background' => 'FFCCCC'),
            'green' => array('color' => '', 'background' => 'CCFF99'),
            'lightgreen' => array('color' => '', 'background' => 'CCFFCC'),
            'yellow' => array('color' => '', 'background' => 'FFFF99'),
            'white' => array('color' => '', 'background' => 'FFFFFF'),
            'grey' => array('color' => '000000', 'background' => '808080'),
            'greywhite' => array('color' => 'FFFFFF', 'background' => '808080'),
            'blue' => array('color' => 'FFFFFF', 'background' => 'blue'),
            'blueblack' => array('color' => '000000', 'background' => 'blue'),
            'lightblue' => array('color' => 'FFFFFF', 'background' => '6666FF'),
            'notice' => array('color' => '514721', 'background' => 'FFF6BF'),
            'header' => array('color' => 'FFFFFF', 'background' => '519CC6'),
            'headerblack' => array('color' => '000000', 'background' => '519CC6'),
            'odd' => array('color' => '', 'background' => 'E5F1F4'),
            'even' => array('color' => '', 'background' => 'F8F8F8'),
        );

    if (empty($code)) return $cssClass;
    elseif (isset($cssClass[$code])) return $cssClass[$code];
    else return '[]';
}

function excelColumnName($index)
{
    --$index;
    if ($index >= 0 && $index < 26)
        return chr(ord('A') + $index);
    else if ($index > 25)
        return (excelColumnName($index / 26)) . (excelColumnName($index % 26 + 1));
    else
        throw new Exception("Invalid Column # " . ($index + 1));
}


function log_error($name, $text) {
    if(!file_exists('./log')){
        mkdir('./log');
    }
    $myfile = fopen("./log/" . $name . ".txt", "a+") or die("Unable to open file!");
    fwrite($myfile, $text . "---------" . date("Y-m-d G:i:s") . "\r\n");
    fclose($myfile);
}

    /**
     * 系统邮件发送函数
     * @param string $tomail 接收邮件者邮箱
     * @param string $name 接收邮件者名称
     * @param string $subject 邮件主题
     * @param string $body 邮件内容
     * @param string $attachment 附件列表
     * @return boolean
     * @author static7 <static7@qq.com>
     */
    function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
        $mail = new \PHPMailer();           //实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                    // 设定使用SMTP服务
        $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';          // 使用安全协议
        $mail->Host = "smtp.exmail.qq.com"; // SMTP 服务器
        $mail->Port = 465;                  // SMTP服务器的端口号
        $mail->Username = "736038880@qq.com";    // SMTP服务器用户名
        $mail->Password = "193zhanshengziji";     // SMTP服务器密码
        $mail->SetFrom('736038880@qq.com', '文明博客');
        $replyEmail = '';                   //留空则为发件人EMAIL
        $replyName = '';                    //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($tomail, $name);
        if (is_array($attachment)) { // 添加附件
            foreach ($attachment as $file) {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }