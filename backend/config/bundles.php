<?php

return [
    
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    DentalOffice\UserBundle\DentalOfficeUserBundle::class => ['all' => true],
    DentalOffice\PatientBundle\DentalOfficePatientBundle::class => ['all' => true],
    DentalOffice\MedicalRecordBundle\DentalOfficeMedicalRecordBundle::class => ['all' => true],
    DentalOffice\AppointmentSchedulingBundle\DentalOfficeAppointmentSchedulingBundle::class => ['all' => true],
    DentalOffice\AppointmentSchedulingBundle\DentalOfficeAppointmentSchedulingBundle::class => ['all' => true],
    DentalOffice\PaymentsBundle\DentalOfficePaymentsBundle::class => ['all' => true],
    DentalOffice\SharedBundle\DentalOfficeSharedBundle::class => ['all' => true],
    DentalOffice\InvoiceBundle\DentalOfficeInvoiceBundle::class => ['all' => true],


];
