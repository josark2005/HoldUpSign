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
** Version: 2.0.8.1701
** Issue:
**      1、修正最后一行长度为col时算法归零的错误
*/
if( isset($_POST['t']) && !empty($_POST['t']) ){
  define("IS_DEBUG",false);  // 是否开启调试模式
  define("EOF","\r\n");     // 定义换行符
  header("Content-Type: text/html; charset=utf-8");
  if( IS_DEBUG == false ){
    header('Content-Type: image/png;');
  }
  // 用户设置
  $text = $_POST['t'];   // 欲输出文本
  //动态设置
  $col = 8;                       // 每行最高数量
  $offset_left = 0;
  $offset_right = 0;
  $offset_top = 0;
  $offset_bottom = 0;
  $in_h = 25;                                 // 高度差（垂直偏移）
  $in_w = 35;                                 // 宽度差（水平偏移）
  $font = 'fonts/msyh.ttf';
  // 算法
  $strlen = mb_strlen(str_replace(EOF,"",$text),"utf-8"); // 计算文本长度
  $text = explode(EOF,$text);
  $row = getrow($text,$col);                      // 计算真实行数
  $inverse_h = 165-$in_h;
  $inverse_w = 80-$in_w;
  // 创建主画布
  $img_p = getwidthAheight($row,$col,$in_w,$in_h,$inverse_w,$inverse_h,$offset_left,$offset_top,$text);
  $img_w = $img_p['w'] + 80 + $offset_right;
  $img_h = $img_p['h'] + 165 + $offset_bottom;
  // 创建画布
  $image = imagecreatetruecolor($img_w,$img_h);
  // 画布背景
  imagefill($image,0,0,imagecolorAllocate($image,255,255,255));
  // 设置透明
  imagecolortransparent($image,imagecolorAllocateAlpha($image,255,255,255,127));
  //字牌文本颜色
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
    if($in_col == 9 && $fix_n != 1){
      $in_col = 1;
      $in_row++;
    }
    // 动态调整列位置
    if($fix_n == 1){
      $in_col = 1;
      $fix_n = 0;
      $in_row ++;
    }
    // 创建文字画布
    $image_in = createTextImg($char ,$str_color ,$font);
    //随机背景颜色
    //imagefill($image_in,0,0,imagecolorAllocate($image_in,rand(0,255),rand(0,255),rand(0,255)));
    $position = getposition($row,$col,$in_w,$in_h,$inverse_w,$inverse_h,$offset_left,$offset_top,$in_row,$in_col);
    $x = $position['x'];
    $y = $position['y'];
    // 复制文字画布到主画布
    imagecopy($image,$image_in,$x,$y,0,0,80,165);
    // 释放临时资源
    imagedestroy($image_in);
    if( IS_DEBUG == true){
      imagepng($image,"./test/{$i}.png");
    }
  }
  if( IS_DEBUG == false){
    imagepng($image);
  }


} else {

  // 载入模板文件
  include "./index.tpl";

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
** @param  array       array
** @param  t           int   当前行数
** @param  fix_row     int   修正行数
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

  /*
  ** 获取真实行数
  ** @param  text array
  ** @param  col  int
  ** @return int
  */
  function getrow($text,$col){
    $l = 0; // 初始化当前行数
    $p = 0; // 初始化当前位置
    for($i = 0; $i < count($text); $i++){
      $sl = mb_strlen($text[$i],"utf-8");  // 当前array[i]中的文本长度
      // 基本行数计算
      $l += ceil($sl/$col);
      // 高级换行计算
      $p = $sl % $col;
      if( $p == 0 && !isset($text[$i]) ){
        $l -- ;
      }
    }
    return $l;
  }

  /*
  ** 获取画布真实宽度与高度
  ** @param  text array
  ** @param  col int
  ** @return array
  */
  function getwidthAheight($row,$col,$in_w,$in_h,$inverse_w,$inverse_h,$offset_left,$offset_top,$text){
    $width = 0;  // 最宽值
    $height = 0; // 最高值
    $l = 0;     // 当前行
    $p = 1;     // 当前位置
    $c = count($text);
    //-for
    for($i = 0; $i < $c; $i++){
      $sl = mb_strlen($text[$i],"utf-8");  // 当前array[i]中的文本长度
      // 基本行数计算
      $temp_l = ceil($sl/$col);  // 增加行数
      // 高级行数计算
      $p = $sl % $col;
      if( $p == 0 && !isset($text[$i]) ){
        $temp_l -- ;
      }
      // 循环计算各行末尾
      for($t = 0; $t < $temp_l; $t++){
        $l ++;  //修改当前行
        // 位置判断
        if($temp_l == 1){
          $p = mb_strlen($text[$i],"utf-8");  //最大位置
          $w = getposition($row,$col,$in_w,$in_h,$inverse_w,$inverse_h,$offset_left,$offset_top,$l,$p);
          if($w['x'] > $width){
            $width = $w['x'];
          }
          if($w['y'] > $height){
            $height = $w['y'];
          }
        }else if($temp_l != 0){
          if($t < $temp_l-1 ){
            $p = 8;  //最大位置
          }else{
            $p = ($sl % $col == 0) ? 8 : $sl % $col ;
          }
          $w = getposition($row,$col,$in_w,$in_h,$inverse_w,$inverse_h,$offset_left,$offset_top,$l,$p);
          if($w['x'] > $width){
            $width = $w['x'];
          }
          if($w['y'] > $height){
            $height = $w['y'];
          }
        }
      }
      //
    }
    //-forEnd
    $po = array(
      "w" => $width,
      "h" => $height
    );
    return $po;
  }

  /*
  ** 位置计算
  ** @param  in_row  int 当前行
  ** @param  in_col  int 当前列
  ** @return array
  */
  function getposition($row,$col,$in_w,$in_h,$inverse_w,$inverse_h,$offset_left,$offset_top,$in_row,$in_col){
    $tl_left = ($row-$in_row)*$in_w;  //左·行偏移量
    $te_left = ($in_col-1)*$inverse_w;  //左·单个偏移
    $te_top = ($in_col-1)*$in_h+($in_row-1)*2*$in_h; //上·单个偏移
    // x轴位置：左留空值+左行动态偏移+左列动态偏移
    $x = $offset_left+$tl_left+$te_left;
    // y轴位置：顶留空值+顶列动态偏移
    $y = $offset_top+$te_top;
    $p = array(
      "x" =>  $x,
      "y" =>  $y
    );
    return $p;
  }

?>
