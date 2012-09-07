<?php
/*
 **  说明：此文件为apc导出文件，只需要在导出服存在。在生产服务器只需要bin文件，即可。
 **  需要保证导出服务器的web路径跟目标运行服务器的web路径一致
 **  导出：要确认 wwwroot同级目录下，存在bin目录，之后，执行此文件(放在tool目录下)，出现“Apc cache done!”即视为成功，之后将bin文件以及其他所有文件(多数已经被清空)复制到目标服务器。
 **  导入：在php.ini中新增apc.preload_binfile="/data/xyws.bin" 即可
 **  AUthor CFC4N cfc4n@cnxct.com $Id: apc_dump.php 1523 2012-08-15 11:52:03Z cfc4n $
 */
define('PROJECTROOT',substr(dirname(__FILE__), 0, -13).DIRECTORY_SEPARATOR);
define('SYSTEMROOT',PROJECTROOT.'system'.DIRECTORY_SEPARATOR);
define('WWWROOT',PROJECTROOT.'wwwroot'.DIRECTORY_SEPARATOR);
define('SERVERROOT',PROJECTROOT.'server'.DIRECTORY_SEPARATOR);
define('APCBIN',PROJECTROOT.'bin'.DIRECTORY_SEPARATOR);
define('BINNAME','xyws.bin');
//定义需要cache的目录
$arrCacheDir = array();
/* AMF核心模块 缓存目录*/
array_push($arrCacheDir,PROJECTROOT.'core');

/* WWWROOT 缓存目录*/
array_push($arrCacheDir,WWWROOT.'controllers',WWWROOT.'helpers',WWWROOT.'hooks',WWWROOT.'languages',WWWROOT.'libraries',WWWROOT.'models');

/* 框架缓存目录 */
array_push($arrCacheDir,SYSTEMROOT.'config',SYSTEMROOT.'database',SYSTEMROOT.'errors',SYSTEMROOT.'helpers',SYSTEMROOT.'kernel',SYSTEMROOT.'libraries');


/* 后台守护进程缓存目录 cli模式运行，务必不要缓存 脚本目录，同时在system\server.php中 加入apc_bin_loadfile APC导出的bin文件 */
//array_push($arrCacheDir,SERVERROOT.'daemon',SERVERROOT.'day',SERVERROOT.'helpers',SERVERROOT.'hour',SERVERROOT.'languages',SERVERROOT.'libraries',SERVERROOT.'minute',SERVERROOT.'models',SERVERROOT.'tools');
array_push($arrCacheDir,SERVERROOT.'helpers',SERVERROOT.'languages',SERVERROOT.'libraries',SERVERROOT.'models');
$arrCacheFile = $arrCacheFileFailed = array();






$arrDropTrue = array();
/*
//$arrDrop = array('controllers/RoleKnight.php', 'controllers/RoleWish.php', 'controllers/tools/Test.php', 'controllers/RoleInfo.php', 'controllers/RolePet.php', 'controllers/RoleExplore.php', 'controllers/RoleVilla.php', 'controllers/RoleInn.php', 'models/MRoleArena.php', 'models/MRoleChapter.php', 'models/MRoleCardItems.php', 'models/MRoleExplore.php', 'models/MRoleItems.php','helpers/post.php','libraries/ApiLib.php','libraries/QRedis.php','libraries/AMFSerializer.php');
$arrDropTrue = array();
foreach ($arrDrop as $key => $value)
{
    $arrDropTrue[$key] = PROJECTROOT.$value;
}
*/

/* bin 目的目录检测*/
if (!is_dir(APCBIN))
{
    exit(APCBIN.' is not directory!!!');
}


/* 循环读取需要cache的目录*/
foreach ($arrCacheDir as $value)
{
    compileDir($value);
}
apc_clear_cache ('user'); //清空导出之前的缓存
/* 生成bin文件*/
 if (!writeBin())
{
    exit('write to '.APCBIN.BINNAME.' failed!!!');
}
$strBinMd5 = md5_file(APCBIN.BINNAME);
/* 未被缓存的php警告*/

foreach ($arrCacheFileFailed as $value)
{
    echo $value," cant't to cached .....";
}


/* 清空被缓存文件*/

if (replaceFile($strBinMd5))
{
    exit('Apc cache done!');
}


function compileDir ($dir)
{
    global $arrCacheFile, $arrCacheFileFailed;
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..')
            {
                //echo $file,"<br />";
                if (is_dir($dir.DIRECTORY_SEPARATOR.$file))
                {
                    compileDir($dir.DIRECTORY_SEPARATOR.$file);
                }
                elseif (is_file($dir.DIRECTORY_SEPARATOR.$file) && $file != '.svn')
                {
                    $arrFileinfo = pathinfo($dir.DIRECTORY_SEPARATOR.$file);
                    if (isset($arrFileinfo['extension']) && $arrFileinfo['extension'] == 'php')
                    {
                        // 检测文件是否为空(是否为已经导出过，防止导出成空文件的情况)
                        if (preg_match('/\[([a-z0-9]{32})\]/',file_get_contents($dir.DIRECTORY_SEPARATOR.$file)))
                        {
                            exit('This file look like dumped,Please check it.');
                        }

                        //开始缓存文件
                        if (apc_compile_file($dir.DIRECTORY_SEPARATOR.$file))
                        {
                            $arrCacheFile[] = $dir.DIRECTORY_SEPARATOR.$file;
                        }
                        else
                        {
                            $arrCacheFileFailed[] = $dir.DIRECTORY_SEPARATOR.$file;
                        }
                    }
                    else
                    {
                        //非php拓展名的文件
                    }
                }
                else
                {
                    // 非文件、非目录。。几乎不可能
                }
            }
        }
        closedir($handle);
    }
    else
    {
        exit('Can\'t to opendir '.$dir);
    }
}



function writeBin ()
{
    global $arrCacheFile, $arrDropTrue ;
    foreach ($arrCacheFile as $key => $value)
    {
        if (in_array($value,$arrDropTrue))
        {
            unset($arrCacheFile[$key]);
        }
    }
    return apc_bin_dumpfile($arrCacheFile, array(), APCBIN.BINNAME);
}


function replaceFile ($strBinMd5)
{
    global $arrCacheFile;
    $strTime = date('Y-m-d H:i:s');
    foreach ($arrCacheFile as $value)
    {
        if (!file_put_contents($value,"<?php\n/* \nThere is nothing,But do not be surprised,It's worked!\nCreated by JoyWay W1 Team (cfc4n@cnxct.com) at {$strTime}.\nThis file md5 : [".md5_file($value)."]\n".BINNAME." md5  : [{$strBinMd5}].\n*/\n?>"))
        {
            echo 'file can\'t to clear empty'.$value;
            return false;
        }
    }
    return true;
}
?>