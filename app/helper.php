<?php

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

function allowed_roles($roles, $msg = 'FORBIDDEN', $code = 403)
{
    if ($user = Auth::user()) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (!in_array($user->role, $roles)) {
            abort($code, $msg);
        }
    }
}

function encrypt_id($string)
{
    return base64_encode($string);
}

function decrypt_id($string)
{
    return base64_decode($string);
}

function wa_me_url($phone, $message = '')
{
    if (empty($phone) || strlen($phone) > 15) {
        return '#';
    }

    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('-', '', $phone);
    if (substr($phone, 0, 1) == 0) {
        $phone = '62' . substr($phone, 1, strlen($phone));
    }
    return 'http://wa.me/' . $phone . '?text=' . $message;
}

function datetime_range_today()
{
    return [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')];
}

function datetime_range_yesterday()
{
    $datetime = strtotime("-1 days");
    return [date('Y-m-d 00:00:00', $datetime), date('Y-m-d 23:59:59', $datetime)];
}

function datetime_range_this_week()
{
    $start = strtotime('next Monday -1 week');
    $start = date('w', $start) == date('w') ? strtotime(date("Y-m-d", $start) . " +7 days") : $start;
    $end = strtotime(date("Y-m-d", $start) . " +6 days");
    return [date('Y-m-d 00:00:00', $start), date('Y-m-d 23:59:59', $end)];
}

function datetime_range_previous_week()
{
    $previous_week = strtotime("-1 week +1 day");

    $start_week = strtotime("last sunday midnight", $previous_week);
    $end_week = strtotime("next saturday", $start_week);

    return [date('Y-m-d 00:00:00', $start_week), date('Y-m-d 23:59:59', $end_week)];
}

function datetime_range_this_month()
{
    return [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')];
}

function datetime_range_previous_month()
{
    $end = strtotime("last day of previous month");
    return [date('Y-m-01 00:00:00', $end), date('Y-m-d 23:59:59', $end)];
}

function years($from, $to)
{
    $years = [];
    for ($y = $from; $y <= $to; $y++) {
        $years[] = $y;
    }
    return $years;
}

function months()
{
    $months = [];

    for ($m = 1; $m <= 12; $m++) {
        $months[$m] = month_names($m);
    }

    return $months;
}

function current_user_id()
{
    return Auth::user()->id;
}

function empty_string_to_null(&$arr, $key)
{
    if (is_array($key)) {
        foreach ($key as $k) {
            empty_string_to_null($arr, $k);
        }
    }

    if (is_string($key) && empty($arr[$key])) {
        $arr[$key] = null;
    }
}

function current_date()
{
    return date('Y-m-d');
}

function current_time()
{
    return date('H:i:s');
}

function current_datetime()
{
    return date('Y-m-d H:i:s');
}

function time_from_datetime($datetime)
{
    $a = explode(' ', $datetime);
    return $a[1];
}

function date_from_datetime($datetime)
{
    $a = explode(' ', $datetime);
    return $a[0];
}

function fill_with_default_value(&$array, $keys, $default)
{
    foreach ($keys as $key) {
        if (empty($array[$key])) {
            $array[$key] = $default;
        }
    }
}

function number_from_input($input)
{
    return floatval(str_replace(',', '.', str_replace('.', '', $input)));
}

// function ensure_user_can_access($resource, $message = 'ACCESS DENIED', $code = 403)
// {
//     /** @disregard P1009 */
//     if (!Auth::user()->canAccess($resource))
//         abort($code, $message);
// }

function datetime_from_input($str)
{
    $input = explode(' ', $str);
    $date = explode('-', $input[0]);

    $out =  "$date[2]-$date[1]-$date[0]";
    if (count($input) == 2) {
        $out .=  " $input[1]";
    }

    return $out;
}

function extract_daterange($daterange)
{
    if (preg_match("/^([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])) - ([0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]))$/", $daterange, $matches)) {
        return [$matches[1], $matches[4]];
    }
    return false;
}

function extract_daterange_from_input($daterange)
{
    if (preg_match("/^((0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}) - ((0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4})$/", $daterange, $matches)) {
        return [$matches[1], $matches[4]];
    }
    return false;
}

function format_number($number, int $prec = 0)
{
    return number_format(floatval($number), $prec, ',', '.');
}

function str_to_double($str)
{
    return doubleVal(str_replace('.', '', $str));
}

function str_to_int($str)
{
    return intVal(str_replace('.', '', $str));
}

function format_datetime($date, $format = 'd/m/Y H:i:s')
{
    if (!$date) {
        return '?';
    }

    if (!$date instanceof Carbon) {
        $date = new Carbon($date);
    }

    return $date->translatedFormat($format);
}

function format_date($date, $format = 'd/m/Y')
{
    if (!$date instanceof Carbon) {
        $date = new Carbon($date);
    }
    return $date->translatedFormat($format);
}

function month_names($month)
{
    switch ((int)$month) {
        case 1:
            return "Januari";
        case 2:
            return "Februari";
        case 3:
            return "Maret";
        case 4:
            return "April";
        case 5:
            return "Mei";
        case 6:
            return "Juni";
        case 7:
            return "Juli";
        case 8:
            return "Agustus";
        case 9:
            return "September";
        case 10:
            return "Oktober";
        case 11:
            return "November";
        case 12:
            return "Desember";
    }
}

function resolve_period(string $period, ?string $start = null, ?string $end = null): array
{
    $today = Carbon::today();
    $now = Carbon::now();

    switch ($period) {
        case 'today':
            $start_date = $today;
            $end_date = $today->copy()->endOfDay();
            break;

        case 'yesterday':
            $start_date = $today->copy()->subDay();
            $end_date = $start_date->copy()->endOfDay();
            break;

        case 'this_week':
            $start_date = $today->copy()->startOfWeek();
            $end_date = $now;
            break;

        case 'last_week':
            $start_date = $today->copy()->subWeek()->startOfWeek();
            $end_date = $start_date->copy()->endOfWeek();
            break;

        case 'this_month':
            $start_date = $today->copy()->startOfMonth();
            $end_date = $now;
            break;

        case 'last_month':
            $start_date = $today->copy()->subMonthNoOverflow()->startOfMonth();
            $end_date = $start_date->copy()->endOfMonth();
            break;

        case 'this_year':
            $start_date = $today->copy()->startOfYear();
            $end_date = $now;
            break;

        case 'last_year':
            $start_date = $today->copy()->subYear()->startOfYear();
            $end_date = $start_date->copy()->endOfYear();
            break;

        case 'last_7_days':
            $start_date = $today->copy()->subDays(6);
            $end_date = $now;
            break;

        case 'last_30_days':
            $start_date = $today->copy()->subDays(29);
            $end_date = $now;
            break;

        case 'custom':
            $start_date = $start ? Carbon::parse($start) : $today;
            $end_date = $end ? Carbon::parse($end) : $now;
            break;

        case 'all_time':

        default:
            $start_date = null;
            $end_date = null;
            break;
    }

    return [$start_date->toDateString(), $end_date->toDateString()];
}
