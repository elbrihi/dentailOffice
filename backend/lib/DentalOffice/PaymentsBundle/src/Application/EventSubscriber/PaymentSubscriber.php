<?php

namespace DentalOffice\PaymentsBundle\Application\EventSubscriber;

use DentalOffice\AppointmentSchedulingBundle\Domain\Event\VisitCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSubscriber implements EventSubscriberInterface
{

   public static function getSubscribedEvents(): array
   {
        return [
            VisitCreatedEvent::class => 'onAppointmentCompleted'
        ];
   }
}