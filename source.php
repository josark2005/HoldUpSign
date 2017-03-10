<?php
// +----------------------------------------------------------------------
// | Writed by Jokin [ Think & Do & Be Better ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 Jokin All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Jokin <327928971@qq.com>
// +----------------------------------------------------------------------
/*
** Version: 2.0.3.1003
** Issue:
**      1、增加手动换行
**      2、修复字体斜率
**      3、修复调整每行最高数量后算法的问题
**      4、规范、优化代码
**      5、修复算法问题
*/
// IO Laungher
  // Notice:运营时请将Debug调至false！！！
  define("IS_DEBUG",false);  // 是否开启调试模式
  define("EOF","\r\n");      // 换行符标记
  header("Content-Type: text/html; charset=utf-8");
  if( IS_DEBUG == false ){
    header('Content-Type: image/png;');
  }
// 用户设置
  $text = "123\r\n4567812345678";   // 欲输出文本
//动态设置
  $col = 8;                       // 每行最高数量
  $offset_left = 50;
  $offset_right = 50;
  $offset_top = 50;
  $offset_bottom = 50;
  $in_h = 25;                                 // 高度差（垂直偏移）
  $in_w = 35;                                 // 宽度差（水平偏移）
  $font = 'fonts/msyh.ttf';
// 算法
  $strlen = mb_strlen(str_replace(EOF,"",$text),"utf-8"); // 计算文本长度
  $text = explode(EOF,$text);                             // 输出文本处理
  $row = ceil($strlen/$col) + count($text) - 1 ;           // 计算行数
  $inverse_h = 165-$in_h;
  $inverse_w = 80-$in_w;
// 创建主画布
  $img_w = $offset_left+80+($col-1)*$inverse_w+$offset_right+($row-1)*$in_w;  // 主画布宽度
  $img_h = $offset_top+165+($col-1)*$in_h+$offset_bottom+($row-1)*$in_h*2;    // 主画布高度
// 创建画布
  $image = imagecreatetruecolor($img_w,$img_h);
// 画布背景
  imagefill($image,0,0,imagecolorAllocateAlpha($image,255,255,255,127));
// 设置透明
  imagecolortransparent($image,imagecolorAllocateAlpha($image,255,255,255,127));
// 字牌文本颜色
  $str_color = imagecolorAllocate($image,24,14,0);
// 算法调整器
  $fix_row = 0;
  $fix_strlen = 0;
  $fix_n = 0;
  fixer($fix_row ,$fix_strlen ,$fix_n);  //调整器初始化
  $in_col = 0;
  $in_row = 1;
  // 创建文字到主画布
  for ($i=1; $i < $strlen+1; $i++) {
  // 获取char
    $char = getchar($text,$i,$fix_row,$fix_strlen,$fix_n);
  // 判断列位置
    $in_col ++ ;
    if($in_col == 9){
      $in_col = 1;
      $in_row++;
    }
  // 动态调整列位置
    if($fix_n == 1){
      $in_col = 1;
      $fix_n = 0;
      $in_row += $fix_row;
    }
  // 创建文字画布
    $image_in = createTextImg($char ,$str_color ,$font);
  //随机背景颜色
    //imagefill($image_in,0,0,imagecolorAllocate($image_in,rand(0,255),rand(0,255),rand(0,255)));
    $tl_left = ($row-$in_row)*$in_w;  //左·行偏移量
    $te_left = ($in_col-1)*$inverse_w;  //左·单个偏移
    $te_top = ($in_col-1)*$in_h+($in_row-1)*2*$in_h; //上·单个偏移
  // x轴位置：左留空值+左行动态偏移+左列动态偏移
    $x = $offset_left+$tl_left+$te_left;
  // y轴位置：顶留空值+顶列动态偏移
    $y = $offset_top+$te_top;
  // 复制文字画布到主画布
    imagecopy($image,$image_in,$x,$y,0,0,80,165);
  // 释放临时资源
    imagedestroy($image_in);
  }
  if( IS_DEBUG == false){
    imagepng($image);
  }


//-----Functions-----

/*
** 创建文字画布
** @param  str string 单个文字内容
** @return image_in
*/
  function createTextImg($str,$str_color,$font){
  //创建子画布
    $image_in = imagecreatefrompng('images/QP4a5rvW_'.rand(0, 40).'.png');
  //文字填充
    imageTTFtext($image_in,19,-28,23,30,$str_color,$font,$str);
    return $image_in;
  }

/*
** 算法调整器初始化
** @param  &row int 行数
** @return void
*/
  function fixer(&$row ,&$fix_strlen ,&$fix_n){
    $row = 0; // 行数
    $fix_strlen = 0;
    $fix_n = 0;
  }
/*
** 获取char
** @param  void
** @return char string
*/
  function getchar($array ,$t ,&$fix_row ,&$fix_strlen ,&$fix_n){
    for ($i=$fix_row; $i < count($array); $i++) {
      $strlen = mb_strlen($array[$i],"utf-8");
      if( $strlen-$t+$fix_strlen < 0){
        $fix_strlen = $fix_strlen + $strlen;  // 初始化下个循环检索
        $fix_row ++;
        $fix_n = 1;
        continue;
      }else{
        $char = mb_substr($array[$i],$t-$fix_strlen-1,1,"utf-8");
        return $char;
      }
    }
  }
?>
