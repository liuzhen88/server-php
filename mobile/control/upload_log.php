<?php
/**
 * 入口
 */class upload_logControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }



    //Check to see if an error code was generated on the upload attempt
    public function upload_logOP(){
        if($_REQUEST){

            if ($_FILES['userfile']['error'] > 0)
            {
                echo 'Problem: ';
                switch ($_FILES['userfile']['error'])
                {
                    case 1:   echo 'File exceeded upload_max_filesize';
                        break;
                    case 2:   echo 'File exceeded max_file_size';
                        break;
                    case 3:   echo 'File only partially uploaded';
                        break;
                    case 4:   echo 'No file uploaded';
                        break;
                    case 6:   echo 'Cannot upload file: No temp directory specified.';
                        break;
                    case 7:   echo 'Upload failed: Cannot write to disk.';
                        break;
                }
                exit;
            }

            // Does the file have the right MIME type?
//            if ($_FILES['userfile']['type'] != 'txt/plain' && $_FILES['userfile']['type'] != 'application/x-zip-compressed')
//            {
//                echo '对不起，不是所允许的文件格式，只能是text文件或者是zip文件';
//                exit;
//            }


            if (!file_exists('./uploads')){
                mkdir ("../uploads");
            }

            $path=date("Y-m-d");  //获取当前时间
            $dir = '../uploads/'.$path;

            if(!is_dir($dir)){
                mkdir($dir,0777);         //创建文件夹
            }

            // put the file where we'd like it
            $time=time();
            $upfile = "$dir/".$time.$_FILES['userfile']['name'];


            if (is_uploaded_file($_FILES['userfile']['tmp_name']))
            {
                if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $upfile))
                {
                    echo '对不起，移动文件失败';
                    exit;
                }
            }
            else
            {
                echo 'Problem: Possible file upload attack. Filename: ';
                echo $_FILES['userfile']['name'];
                exit;
            }
            /* Model()->table('circle_like')->insert(array('theme_id'=>$theme_id ,'member_id'=>$member_id ,'addtime'=>time() ));*/
            $data=array(
                'user_name'=>$_REQUEST['user_name'],
                'time'=>$time,
                'file_path'=>$upfile
            );
            Model()->table('upload_file')->insert($data);
            output_data('ok');
        }else{
            echo '没有文件';
        }
    }

        /*下载文件*/
    public function down_fileOP(){
        $result=Model()->table('upload_file')->select();
        if(!empty($_GET['id'])){
            $result=Model()->table('upload_file')->find(intval($_GET['id']));
            $filename=$result['file_path'];
            //$filename = "./".$res['path'];
            header('Content-Type:application/octet-stream'); //指定下载文件类型
            header('Content-Disposition: attachment; filename="'.$filename.'"'); //指定下载文件的描述
            header('Content-Length:'.filesize($filename)); //指定下载文件的大小
            //将文件内容读取出来并直接输出，以便下载
            readfile($filename);

        }
        foreach ($result as  $v) {
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
            echo "<div style='text-align:center;margin:10px auto'><a href=./index.php?act=upload_log&op=down_file&id=".$v['id'].'>'.$v['file_path']."</a><div>";
        }
    }

 }
