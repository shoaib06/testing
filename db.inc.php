<?php

$conn = mysqli_connect('localhost', 'yourusername', 'yourpassword', 'stub');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function sql_query($sql){
    global $conn;
    $res=mysqli_query($conn, $sql);
    return $res;
}

function insert_id(){
    global $conn;
    return mysqli_insert_id($conn);
}

function sql_count(&$res){
    return mysqli_num_rows($res);
}

function sql_fetch_array(&$res){
    $row = mysqli_fetch_assoc($res);
    return $row;
}

?>