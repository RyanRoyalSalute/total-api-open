<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketService
{
    /**
     * Generate a list of tickets.
     *
     * @param int $courseId
     * @param int $attendance
     * @param string $tradeNo
     * @param string $date
     * @param string $time
     * @param int $userId
     * @param int $teacherId
     * @param string $ownerPhone
     * @return array
     */
    public function generateTickets(int $courseId, int $attendance, string $tradeNo, string $date, string $time, int $userId, int $teacherId, string $ownerPhone, int $session_id): array
    {
        $tickets = [];

        for ($i = 1; $i <= $attendance; $i++) {
            $ticketId = $this->generateTicketId($courseId, $i, $date, $time);

            $tickets[] = [
                'ticket_id'        => $ticketId,
                'created_by'       => $userId,
                'updated_by'       => $userId,
                'course_id'        => $courseId,
                'ticket_status'    => 0,
                'owner_phone'      => $ownerPhone,
                'owner_user_id'    => $userId,
                'payment_record_id'=> $tradeNo,
                'teacher_id'       => $teacherId,
                'date'             => $date,
                'time'             => $time,
                'session_id'       => $session_id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        return $tickets;
    }

    /**
     * Generate a unique ticket ID.
     * Format: [4-digit courseId][6-digit DATE, yymmdd][4-digit TIME, hhmm][3-digit SEAT, 001]
     *
     * @param int $courseId
     * @param int $index
     * @param string $date
     * @param string $time
     * @return string
     */
    private function generateTicketId(int $courseId, int $index, string $date, string $time): string
    {
        $dateFormatted = Carbon::parse($date)->format('ymd'); // yymmdd
        $timeFormatted = str_replace(':', '', $time); // hhmm

        return str_pad($courseId, 4, '0', STR_PAD_LEFT)
             . $dateFormatted
             . $timeFormatted
             . str_pad($index, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Insert tickets into the database.
     *
     * @param array $tickets
     * @return void
     */
    public function insertTickets(array $tickets): void
    {
        DB::table('course_tickets')->insert($tickets);
    }
}