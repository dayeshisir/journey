<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午1:42
 */
//class Article
//{
//    const HOST = 'localhost';
//    const USER = 'root';
//    const PASSWORD = 'DaYe19!(';
//
//    public static function first()
//    {
//        $connect = mysqli_connect(self::HOST, self::USER, self::PASSWORD);
//
//        if (!$connect) {
//            die("Could not connect: " . mysqli_connect_error());
//        }
//
//        mysqli_set_charset($connect,"UTF8");
//
//        mysqli_select_db($connect,"webdb");
//
//        $result = mysqli_query($connect,"select * from articles limit 0, 1");
//
//        if ($row = mysqli_fetch_assoc($result)) {
////            echo "<h1>" . $row['title'] . "</h1>";
////            echo "<p>" . $row['content'] . "</p>";
//            return $row;
//        } else {
//            return [];
//        }
//
//        mysqli_close($connect);
//    }
//}

class Article extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;
}