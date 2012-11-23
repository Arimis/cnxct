<?php
/*
 **  说明：此文件为apc导出文件，只需要在导出服存在。在生产服务器只需要bin文件，即可。
 **  需要保证导出服务器的web路径跟目标运行服务器的web路径一致
 **  导出：将此文件防项目路径的根目录下，执行此文件，出现“Apc cache done!”即视为成功，之后将dumproot目录下所有文件(多数已经被清空)复制到目标服务器。
 **  导入：在php.ini中新增apc.preload_binfile="/data/xyws.bin" 即可
 **  AUthor CFC4N cfc4n@cnxct.com $Id: apc_dump.php 2449 2012-11-23 05:16:41Z cfc4n $
 */
if (php_sapi_name() == 'cli')
{
    define('NEWLINE',"\n");
}
else
{
    define('NEWLINE','<br />');
}
define('PROJECTROOT',dirname(__FILE__));    //define('PROJECTROOT','/data/htdocs');


/* 定义项目主路径信息 */
define('SYSTEMROOT',PROJECTROOT.DIRECTORY_SEPARATOR.'system');
define('WWWROOT',PROJECTROOT.DIRECTORY_SEPARATOR.'wwwroot');
define('SERVERROOT',PROJECTROOT.DIRECTORY_SEPARATOR.'server');
define('COREROOT',PROJECTROOT.DIRECTORY_SEPARATOR.'core');
define('DUMPROOT',PROJECTROOT.DIRECTORY_SEPARATOR.'dumproot');
define('APCBIN',DUMPROOT.DIRECTORY_SEPARATOR.'bin');
define('BINNAME','xyws.bin');
define('MD5FILENAME','xyws_files.md5');
$strDateTime = date('YmdHis');

//定义需要cache的目录
$arrCacheDir = array(COREROOT,SYSTEMROOT,WWWROOT,SERVERROOT);

/* WWWROOT 缓存目录*/
//array_push($arrCacheDir,WWWROOT.'controllers',WWWROOT.'helpers',WWWROOT.'hooks',WWWROOT.'languages',WWWROOT.'libraries',WWWROOT.'models');
/* 框架缓存目录 */
//array_push($arrCacheDir,SYSTEMROOT.'config',SYSTEMROOT.'database',SYSTEMROOT.'errors',SYSTEMROOT.'helpers',SYSTEMROOT.'kernel',SYSTEMROOT.'libraries');
/* 后台守护进程 */
//array_push($arrCacheDir,SERVERROOT.'daemon',SERVERROOT.'day',SERVERROOT.'helpers',SERVERROOT.'hour',SERVERROOT.'languages',SERVERROOT.'libraries',SERVERROOT.'minute',SERVERROOT.'models',SERVERROOT.'tools');


/* 需要过滤的文件 */
$arrDropFile = array(WWWROOT.DIRECTORY_SEPARATOR.'AmfConfig.php',WWWROOT.DIRECTORY_SEPARATOR.'ResConfig.php');

/* 需要过滤的目录 */
$arrDropDir = array(WWWROOT.DIRECTORY_SEPARATOR.'config',WWWROOT.DIRECTORY_SEPARATOR.'views',WWWROOT.DIRECTORY_SEPARATOR.'tool',WWWROOT.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'amfphp',SERVERROOT.DIRECTORY_SEPARATOR.'config');



/* 以下内容，请勿变更  */
$arrCacheFile = $arrCacheFileFailed = array();

/* bin 目的目录检测*/
if (!is_dir(APCBIN))
{
    if(!mkdir(APCBIN,0777,true))
    {
        echo APCBIN,' can\'t to create!'.NEWLINE;
        exit();
    }
}

apc_clear_cache ('user'); //清空导出之前的缓存

/* 循环读取需要cache的目录*/
foreach ($arrCacheDir as $value)
{
    compileDir($value);
}

/* 生成bin文件*/
 if (!apc_bin_dumpfile($arrCacheFile, array(), APCBIN.DIRECTORY_SEPARATOR.BINNAME.'_'.$strDateTime))
{
    exit('write to '.APCBIN.DIRECTORY_SEPARATOR.BINNAME.'_'.$strDateTime.' failed!!!'.NEWLINE);
}
$strBinMd5 = md5_file(APCBIN.DIRECTORY_SEPARATOR.BINNAME.'_'.$strDateTime);
/* 未被缓存的php警告*/

foreach ($arrCacheFileFailed as $value)
{
    echo $value," cant't to cached .....".NEWLINE;
}


/* 清空被缓存文件*/

replaceFile($strBinMd5);

/* 复制被过滤文件 */
moveDropFile($arrDropFile);

/* 复制被过滤目录 */
moveDropDir($arrDropDir);

exit('Apc cache done!'.NEWLINE);

function compileDir ($dir)
{
    global $arrCacheFile, $arrCacheFileFailed, $arrDropDir, $arrDropFile;
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..')
            {
                if (is_dir($dir.DIRECTORY_SEPARATOR.$file))
                {
                    //exit($dir.DIRECTORY_SEPARATOR.$file);
                    //判读是否为排除目录
                    if (!in_array($dir.DIRECTORY_SEPARATOR.$file,$arrDropDir))
                    {
                        compileDir($dir.DIRECTORY_SEPARATOR.$file);
                    }
                    else
                    {
                        echo 'skip dir :    ',$dir.DIRECTORY_SEPARATOR.$file,NEWLINE;
                    }
                }
                elseif (is_file($dir.DIRECTORY_SEPARATOR.$file) && $file != '.svn')
                {
                    $arrFileinfo = pathinfo($dir.DIRECTORY_SEPARATOR.$file);
                    if (isset($arrFileinfo['extension']) && $arrFileinfo['extension'] == 'php')
                    {
                        //判读是否为排除目录
                        if (in_array($dir.DIRECTORY_SEPARATOR.$file,$arrDropFile))
                        {
                            echo 'skip file:    ', $dir.DIRECTORY_SEPARATOR.$file,NEWLINE;
                            continue;
                        }
                        // 检测文件是否为空(是否为已经导出过，防止导出成空文件的情况)
                        if (preg_match('/ md5 : \[([a-z0-9]{32})\]/',file_get_contents($dir.DIRECTORY_SEPARATOR.$file)))
                        {
                            exit($dir.DIRECTORY_SEPARATOR.$file.'. This file look like dumped,Please check it.'.NEWLINE);
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
        exit('Can\'t to opendir '.$dir.NEWLINE);
    }
}

function replaceFile ($strBinMd5)
{
    global $arrCacheFile, $strDateTime;
    $strTime = date('Y-m-d H:i:s');
    $handle = fopen(APCBIN.DIRECTORY_SEPARATOR.MD5FILENAME.'_'.$strDateTime, 'w');
    if (!$handle)
    {
        exit('Can\'t to create file: '.MD5FILENAME.'_'.$strDateTime.NEWLINE);
    }
    foreach ($arrCacheFile as $value)
    {
        $value1 = str_replace(PROJECTROOT,DUMPROOT,$value);
        $dir = dirname($value1);
        if (!is_dir($dir))
        {
            if(!mkdir($dir,0777,true))
            {
                echo $dir,' can\'t to create!'.NEWLINE;
                exit();
            }
        }
        $filemd5 = md5_file($value);
        if (!file_put_contents($value1,"<?php\n/* \nThere is nothing,But do not be surprised,It's worked!\nCreated by JoyWay W1 Team (cfc4n@cnxct.com) at {$strTime}.\nThis file md5 : [".$filemd5."]\n".BINNAME." md5 : [{$strBinMd5}].\n*/\n?>"))
        {
            echo 'file can\'t write file '.$value1.NEWLINE;
            exit();
        }
        fwrite($handle, $filemd5.' '.str_replace(PROJECTROOT, '*',$value)."\n");
    }
    fclose($handle);
    return true;
}

function moveDropFile ($arrDropFile)
{
    foreach ($arrDropFile as $key => $value)
    {
        if (!copy($value,str_replace(PROJECTROOT,DUMPROOT,$value)))
        {
            echo 'file can\'t copy '.$value.NEWLINE;
        }
    }
    return true;
}


function moveDropDir ($arrDropDir)
{
    foreach ($arrDropDir as $dir)
    {
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..')
                {
                    if (is_dir($dir.DIRECTORY_SEPARATOR.$file) && $file != '.svn')
                    {
                        moveDropDir(array($dir.DIRECTORY_SEPARATOR.$file));
                    }
                    elseif (is_file($dir.DIRECTORY_SEPARATOR.$file) && $file != '.svn')
                    {
                        $newDir = str_replace(PROJECTROOT,DUMPROOT,$dir.DIRECTORY_SEPARATOR.$dir);
                        if (!is_dir($newDir))
                        {
                            if(!mkdir($newDir,0777,true))
                            {
                                echo $newDir,' can\'t to create!'.NEWLINE;
                                exit();
                            }
                        }
                        if (!copy($dir.DIRECTORY_SEPARATOR.$file,str_replace(PROJECTROOT,DUMPROOT,$dir.DIRECTORY_SEPARATOR.$file)))
                        {
                            echo 'file can\'t copy '.$value.NEWLINE;
                        }
                    }
                }
            }
            closedir($handle);
        }
        else
        {
            exit('Can\'t to opendir '.$dir.NEWLINE);
        }
    }
    return true;
}
?>