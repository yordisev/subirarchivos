<?php
 function Connect_FTP()
 {
     $ftp_server = "172.20.0.2";//IP privada oracle tunel - sophos
     $ftp_port = "22";
     $ftp_user = "opc"; // <--- SCP
 
     $pubkeyfile = '/Users/yordis.escorcia/.ssh/id_rsa.pub'; // <---- Usar en equipo local
     $privkeyfile = '/Users/yordis.escorcia/.ssh/id_rsa'; // <---- Usar en equipo local
 
     if (!$con_id = ssh2_connect($ftp_server, $ftp_port)) die('0 - Error al conectar');
     if (!ssh2_auth_pubkey_file($con_id,$ftp_user,$pubkeyfile,$privkeyfile)) die('0 - Error al conectar');
 
     return $con_id;
 }
 
 function UploadFile($dir /*Directorio del proyecto Ejemp: ( carpeta/subcarpeta/... )*/, 
 $file /*Nombre del archivo y su extension Ejemp: ( archivo.zip )*/)//
 {
    //  $root = $_SERVER['DOCUMENT_ROOT'].'/subir/';
     $con_id = Connect_FTP();
     $host_path = '/data/sftpuser/cargue_ftp/Digitalizacion/Genesis/';//Ruta Host
     
     $sftp = ssh2_sftp($con_id); // Abrimos la conexion sftp
     
     $parts = explode('/',$dir); // Calculamos cuantos directorios existen
     foreach($parts as $part){ // Iniciamos el recorrido para crear los directorios si es que no existen algunos
         $host_path = $host_path.$part.'/'; // Concatenamos la ruta $host_path con la carpeta del proyecto
         if(!is_dir("ssh2.sftp://$sftp$host_path")){ // Usamos la conexion sftp creada para Validar si el directorio existe
             mkdir("ssh2.sftp://$sftp$host_path"); // Crea el directorio
         }
     }
 
     if(!is_dir("ssh2.sftp://$sftp$host_path")){ // Validamos si se crearon los directorios
         return '0 - Error al subir el archivo, no se crearon los directorios';
     } else {
        $file = 'C:\xampp\htdocs\subir\archivos_subidos\_1646839505.txt';
         $host_path = $host_path.'archivo.txt'; // Concatenamos la ruta del directorio con el nombre del archivo
        //  $host_path = $host_path.$file; // Concatenamos la ruta del directorio con el nombre del archivo
         $subio = ssh2_scp_send($con_id, $file, $host_path); // Subimos el archivo al servidor
     }
 
     if((!$subio) || (filesize("ssh2.sftp://$sftp$host_path") == 0 )){ return '0 - Archivo no subido correctamente';} // Validamos que se subio el archivo y que el peso sea diferente de 0
 
     ssh2_exec($con_id, 'exit'); // Cerramos la conexion
     return (substr($host_path, 14, strlen($host_path)-1)); // Recortamos la ruta para que solo muestre desde /cargue_ftp/...
 }

if (isset($_FILES['archivo'])) {

    $archivo = $_FILES['archivo'];
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
	$time = time();
    $nombre = "{$_POST['nombre_archivo']}_$time.$extension";


    $path = 'Pruebasubida';
    $day = date("dmY");
    $ruta = $path.'/'.$day;
    print_r($archivo['tmp_name']);
    $subio = UploadFile($ruta, $archivo['tmp_name']);
    if(substr($subio, 0,11) == '/cargue_ftp'){
        echo $subio;
    } else{
        echo json_encode((object) [
            'codigo' => -1,
            'mensaje' => 'No se recibio el archivo, intente subirlo nuevamente.'
        ]);
    }



   

    // if (move_uploaded_file($archivo['tmp_name'], "archivos_subidos/$nombre")) {
    //     echo 1;
    // } else {
    //     echo 0;
    // }
}


     
      
?>
