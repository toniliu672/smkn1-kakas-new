<?php
// auth/auth_check.php
session_start();

function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../unauthorized.php");
        exit();
    }
}

function check_admin()
{
    check_login();
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: ../protected.php");
        exit();
    }
}

function check_surat_access()
{
    check_login();
    if (!in_array($_SESSION['user_role'], ['admin', 'pengelola_surat', 'kepala_sekolah'])) {
        header("Location: ../unauthorized.php");
        exit();
    }
}

function check_kepala_sekolah()
{
    check_login();
    if ($_SESSION['user_role'] !== 'kepala_sekolah') {
        header("Location: ../protected.php");
        exit();
    }
}

function check_pengelola_surat()
{
    check_login();
    if ($_SESSION['user_role'] !== 'pengelola_surat') {
        header("Location: ../unauthorized.php");
        exit();
    }
}
