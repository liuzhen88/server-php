<?php
/**
 * 入口
 */
    header("Content-type: text/html; charset=utf-8");     
    //Check to see if an error code was generated on the upload attempt  
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
      if ($_FILES['userfile']['type'] != 'text/plain' && $_FILES['userfile']['type'] != 'application/x-zip-compressed')  
      {  
        echo '对不起，不是所允许的文件格式，只能是text文件或者是zip文件';  
        exit;  
      }  
      
      
      if (!file_exists('uploads')){
          mkdir ("uploads".0777); 
       }
       
      $path=date("Y-m-d");  //获取当前时间
      $dir = 'uploads/'.$path;

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
      
      output_data('ok');      
     }
    ?>  