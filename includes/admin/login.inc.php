<?php
if(isset($_POST['login_but'])) {
    require '../../helpers/init_conn_db.php';
    $email_id = $_POST['user_id'];
    $password = $_POST['user_pass'];
    $sql = 'SELECT * FROM Admin WHERE admin_uname=? OR admin_email=?';    
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare($stmt,$sql);
    mysqli_stmt_bind_param($stmt,'ss',$email_id,$email_id);            
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);    
    if($row = mysqli_fetch_assoc($result)) {        
        $pwd_check = password_verify($password,$row['admin_pwd']);
        if($pwd_check == false) {
            header('Location: ../../admin/login.php?error=wrongpwd');
            exit();
        }
        else if($pwd_check == true) {
            session_start();
            $_SESSION['adminId'] = $row['admin_id'];
            $_SESSION['adminUname'] = $row['admin_uname'];
            $_SESSION['adminEmail'] = $row['admin_email'];
            header('Location: ../../admin/index.php?login=success');
            exit();
        } else {
            header('Location: ../../admin/login.php?error=sqlerror');
            exit();
        }
    } else {
        header('Location: ../../admin/login.php?error=invalidcred');
        exit();
    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    header('Location: ../../index.php');
    exit();
}
