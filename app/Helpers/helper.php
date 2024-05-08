<?php

// namespace App\Helpers;


class Helper
{
    public function bladeHelper($someValue)
    {
        return "increment $someValue";
    }

    function getCurrencyFormatted($_input, $_currency)
    {
        return number_format($_input);
        $fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
        $fmt->setAttribute($fmt::FRACTION_DIGITS, 2);
        return $fmt->formatCurrency($_input, $_currency);
    }

    function getExtraWorkedMoney($assignment)
    {
        $night_shift_threshold = 23;
        $morning_shift_threshold = 7;

        $special_days_collection = collect(
            [
                ["day" => 1, "month" => 1],
                ["day" => 17, "month" => 04],
                ["day" => 18, "month" => 04],
                ["day" => 27, "month" => 04],
                ["day" => 26, "month" => 05],
                ["day" => 05, "month" => 06],
                ["day" => 06, "month" => 06],
                ["day" => 25, "month" => 12],
                ["day" => 26, "month" => 12]
            ]
        );



        $start_to_work = \Carbon\Carbon::parse($assignment->start_date);
        $start_to_work_time = \Carbon\Carbon::parse($assignment->time_from);
        $start_to_work_time->year($start_to_work->year);
        $start_to_work_time->month($start_to_work->month);
        $start_to_work_time->day($start_to_work->day);
        $start_to_work_time_copy = $start_to_work_time->copy();

        $end_of_work = \Carbon\Carbon::parse($assignment->end_date);
        $end_of_work_time = \Carbon\Carbon::parse($assignment->time_to);
        $end_of_work_time->year($end_of_work->year);
        $end_of_work_time->month($end_of_work->month);
        $end_of_work_time->day($end_of_work->day);

        $worked_diff_in_minutes = $start_to_work_time->diffInMinutes($end_of_work_time);
        $worked_diff_in_half_hour = $worked_diff_in_minutes / 30;

        $counter = 0;
        for ($i = 0; $i < $worked_diff_in_half_hour; $i++) {
            $start_to_work_time_copy->addMinutes(30);
            /* check the specific */
            if ($special_days_collection->where("day", $start_to_work_time_copy->day)->where("month", $start_to_work_time_copy->month)->count() > 0) {
                $counter++;
            } else {
                if ($start_to_work_time_copy->hour > $night_shift_threshold) {
                    $counter++;
                } else if ($start_to_work_time_copy->hour == $night_shift_threshold && $start_to_work_time_copy->minute == 30) {
                    $counter++;
                } else if ($start_to_work_time_copy->hour < $morning_shift_threshold) {
                    $counter++;
                } else if ($start_to_work_time_copy->hour == $morning_shift_threshold && $start_to_work_time_copy->minute == 0) {
                    $counter++;
                }
            }
        }

        /* end of time calculator */
        if ($counter) {
            $extra_price = $counter * 30 * ($assignment->payrate / 5 / 60);
        } else {
            $extra_price = 0;
        }

        return $extra_price;
    }

    public static function instance()
    {
        return new Helper();
    }
}
