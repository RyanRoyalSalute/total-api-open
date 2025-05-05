<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\DiscountService;

class PaymentUtilService
{
    protected $ticketService;
    protected $discountService;

    public function __construct(TicketService $ticketService, DiscountService $discountService)
    {
        $this->ticketService = $ticketService;
        $this->discountService = $discountService;
    }

    /**
     * Process a successful payment: generate tickets, update seats, and handle discounts.
     *
     * @param array $payment
     * @return void
     * @throws \Exception
     */
    public function processPaymentSuccess(array $payment): void
    {
        // Generate and insert tickets
        $tickets = $this->ticketService->generateTickets(
            $payment['course_id'],
            $payment['attendance'],
            $payment['trade_no'],
            $payment['date'],
            $payment['time'],
            $payment['user_id'],
            $payment['teacher_id'],
            $payment['owner_phone'],
            $payment['session_id']
        );
        $this->ticketService->insertTickets($tickets);

        // Move blocked seats to booked
        $this->updateCourseSessionSeats($payment['session_id'], $payment['attendance']);

        // Handle discount if privileged_level is present and greater than 0
        if ($payment['privileged_level'] > 0) {
            $rechargeId = DB::table('recharges')
                ->where('id', $payment['privileged_level'])
                ->where('active', true)
                ->value('id');
            if ($rechargeId) {
                $this->discountService->handleDiscount($payment['user_id'], $rechargeId, $payment['id']);
            }
        }
    }

    /**
     * Release blocked seats on payment failure or cancellation.
     *
     * @param int $sessionId
     * @param int $attendance
     * @return void
     */
    public function releaseBlockedSeats(int $sessionId, int $attendance): void
    {
        DB::table('course_sessions')
            ->where('id', $sessionId)
            ->decrement('blocked_seats', $attendance, ['updated_at' => now()]);
    }

    /**
     * Update course session seats from blocked to booked.
     *
     * @param int $sessionId
     * @param int $attendance
     * @return void
     */
    private function updateCourseSessionSeats(int $sessionId, int $attendance): void
    {
        DB::table('course_sessions')
            ->where('id', $sessionId)
            ->update([
                'booked_seats' => DB::raw('booked_seats + ' . $attendance),
                'blocked_seats' => DB::raw('blocked_seats - ' . $attendance),
                'updated_at' => now()
            ]);
    }
}