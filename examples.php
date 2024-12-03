<?php

require_once "vendor/autoload.php";

use Kindy\HijriDate\HijriDateTime;
use Kindy\HijriDate\Calendar;
use Kindy\HijriDate\CalendarAdjustment;

//echo new HijriDateTime();
//echo (new HijriDateTime())->format('D _j _M _Yهـ (j-m-Yم)');

//echo HijriDateTime::Date('_Y-_m-_d','2024-12-3');

//echo (new HijriDateTime("2024-12-3"))->format('D _j _M _Yهـ (j-m-Yم)');

//$d = HijriDateTime::createFromHijri(1446,6,2);
//echo $d->format('D _j _M _Yهـ (j-m-Yم)');

//$date = new HijriDateTime();
//echo $date;
//$date->setDateHijri(1445,9,1);
//echo $date;


//$calendar = new Calendar([
//     'umalqura'=> TRUE,
//     'adj_data' => '1426 => 57250, 1429 => 57339,'
// ]);
// echo $calendar->JDToHijri(2450000);

//echo (new Calendar())->days_in_month(9, 1436);

echo HijriDateTime::Date('_Y-_m-_d','2024-12-3');