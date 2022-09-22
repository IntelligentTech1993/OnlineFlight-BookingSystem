<?php
session_start();
if(isset($_SESSION['adminId']) && isset($_POST['signup_submit'])) {    
    require '../../helpers/init_conn_db.php';
    $username = $_POST['username'];
    $email_id = $_POST['email_id'];
    $password = $_POST['password'];
    $password_repeat = $_POST['password_repeat'];
    if(!filter_var($email_id,FILTER_VALIDATE_EMAIL)) {
        header('Location: ../../admin/register.php?error=invalidemail');
        exit();
    }
    else if($password !== $password_repeat) {
        header('Location: ../../admin/register.php?error=pwdnotmatch');
        exit();
    }
    else {
        $username_sql = 'SELECT admin_uname FROM Admin WHERE admin_uname=?';
        $email_sql = 'SELECT admin_email FROM Admin WHERE admin_email=?';
        $stmt_uname = mysqli_stmt_init($conn);
        $stmt_email = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt_uname,$username_sql);
        mysqli_stmt_bind_param($stmt_uname, "s", $username);
        mysqli_stmt_execute($stmt_uname);
        mysqli_stmt_store_result($stmt_uname);            
        $username_check = mysqli_stmt_num_rows($stmt_uname);
        if($username_check > 0) {
            header('Location: ../../admin/register.php?error=usernameexists');
            exit();
        } else {
            mysqli_stmt_prepare($stmt_email,$email_sql);
            mysqli_stmt_bind_param($stmt_email, "s", $email_id);
            mysqli_stmt_execute($stmt_email);
            mysqli_stmt_store_result($stmt_email);
            $email_check = mysqli_stmt_num_rows($stmt_email);
            if($email_check > 0) {
                header('Location: ../../admin/register.php?error=emailexists');
                exit();
            } else {
                mysqli_stmt_close($stmt_uname);
                mysqli_stmt_close($stmt_email);
                $sql = "INSERT INTO Admin(admin_uname,admin_email,admin_pwd) VALUES
                  (?,?,?)";
                $pwd_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt,$sql);
                mysqli_stmt_bind_param($stmt,'sss',$username,$email_id,$pwd_hash);            
                mysqli_stmt_execute($stmt);  
                mysqli_stmt_close($stmt);

                // Sign in the user
                $sql = 'SELECT * FROM Admin WHERE admin_uname=? OR admin_email=?';
                $stmt = mysqli_stmt_init($conn);
                mysqli_stmt_prepare($stmt,$sql);
                mysqli_stmt_bind_param($stmt,'ss',$email_id,$email_id);            
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($result);    
                if($row = mysqli_fetch_assoc($result)) {
                    // $pwd_check = password_verify($password,"$2y$10$5Cu/kAh5asdSvpXlpGOOY.MBjp5qELS/W49BGFU0.s6JLLJue4tOi");
                    $pwd_check = password_verify($password,$row['admin_pwd']);
                    if($pwd_check == false) {
                        header('Location: ../../admin/login.php?error=wrongpwd');
                        exit();
                    }
                    else if($pwd_check == true) {
                        session_start();
                        $_SESSION['adminId'] = $row['admin_id'];
                        $_SESSION['adminUname'] = $row['admin_uname'];
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
            }
        }

    }
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    header('Location: ../register.php');
    exit();
}
