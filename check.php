<?php
/*
**  php check.php md5_file_path PROJECTROOT
**
**  md5_file_path: created by apc_dump.php when do dump binfile.
**  PROJECTROOT: path of XYWS project ,and the same as cached file path in apc binfile
**
**  eg: php check.php /data/htdocs/bin/xyws_files.md5 /data/htdocs
**  $Id: check.php 2416 2012-11-16 06:46:01Z cfc4n $
*/

if (count($argv) < 3)
{
    exit('Usage: php '.$argv[0].' xyws_files.md5! /data/htdocs'."\n");
}

if (!isset($argv[1]))
{
    exit('Usage: php '.$argv[0].' xyws_files.md5! /data/htdocs'."\n");
}

if (!is_file($argv[1]))
{
    exit($argv[1].' is not a file!'."\n");
}

$arrTmp = file($argv[1]);
if ( '/' === substr($argv[2], -1)) {
    $strProject = substr($argv[2], 0,-1);
}
else
{
    $strProject = $argv[2];
}

$arrFile = array();
foreach ($arrTmp as $value)
{
    $arrTmp1 = explode(' ',$value);
    if (count($arrTmp1) == 2)
    {
        $arrFile[$arrTmp1[1]] = $arrTmp1[0];
    }
}
$iNumFile = count($arrFile);
$arr = apc_cache_info();
$arrApc = array();
$bIsRoot = false;
foreach ($arr['cache_list'] as $value)
{
    /*
    **  以后可增加对运维输入的项目路径做判断，被cache文件是否包含此路径
    */
    if (__FILE__ !== $value['filename'])
    {
        $arrApc[str_replace($strProject,'*',$value['filename'])] = $value['md5'];
    }
}
$iNumApc = count($arrApc);

$arrDiff1 = array_diff($arrApc,$arrFile);
$arrDiff2 = array_diff($arrFile,$arrApc);
$arrDiff = array_merge($arrDiff1,$arrDiff2);
if (count($arrDiff) == 0)
{
    echo 'All file were verified(',$iNumFile,'/',$iNumApc,').';
}
else
{
    print_r($arrDiff);
}
echo "\n";
?>