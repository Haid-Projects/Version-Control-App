<?php

namespace App\Services;

use App\Repositories\ReservationRepository;
use App\Repositories\UserRepository;
use App\Traits\GeneralTrait;

class ReservationService
{
    use GeneralTrait;
    protected $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function checkIn(){

    }

    public function checkInBulk(){

    }

    public function checkOut(){

    }
}
