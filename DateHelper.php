<?php
class DateHelper {
    public static function getWeekYear($dateStr = null) {
        if ($dateStr === null) {
            $dateStr = date('Y-m-d');
        }
        
        // Add 1 day to shift Sunday to the next week (ISO-8601 Monday start)
        // This effectively makes Sunday the start of the week
        $timestamp = strtotime($dateStr . ' +1 day');
        
        return [
            'week' => (int)date('W', $timestamp),
            'year' => (int)date('o', $timestamp)
        ];
    }

    public static function getWeekRange($week, $year) {
        // Inverse of getWeekYear to find start and end dates
        // ISO Week starts on Monday. Since we shifted +1 day, 
        // Our "Sunday start" week corresponds to ISO week of (Date + 1 day).
        // So ISO Monday corresponds to our Sunday.
        
        $dto = new DateTime();
        $dto->setISODate($year, $week); // This sets it to Monday of that ISO week
        
        // Monday of ISO week is Sunday of our week
        $startDate = clone $dto;
        $startDate->modify('-1 day'); // Back to Sunday
        
        $endDate = clone $startDate;
        $endDate->modify('+6 days'); // Saturday
        
        return [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d')
        ];
    }
}
