<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Patient;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Repository\PatientRepository;
use App\Repository\TreatmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/appointment")
 */
class AppointmentController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var AppointmentRepository
     */
    private $appointmentRepository;
    /**
     * @var PatientRepository
     */
    private $patientRepository;
    /**
     * @var TreatmentRepository
     */
    private $treatmentRepository;

    private $security;

    public function __construct(PatientRepository $patientRepository, AppointmentRepository $appointmentRepository, TreatmentRepository $treatmentRepository, EntityManagerInterface $entityManager,Security $security){
        $this->entityManager = $entityManager;
        $this->appointmentRepository = $appointmentRepository;
        $this->patientRepository = $patientRepository;
        $this->treatmentRepository = $treatmentRepository;
        $this->security = $security;

    }
    
    /**
     * @Route("/", name="appointment_index", methods={"GET"})
     * @param AppointmentRepository $appointmentRepository
     * @return Response
     */
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        $user = $this->security->getUser();
        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointmentRepository->findByCompany($user->getCompany()),
        ]);
    }

    /**
     * @Route("/calendar", name="appointment_calendar", methods={"GET"})
     */
    public function calendar(): Response
    {
        return $this->render('appointment/calendar.html.twig');
    }

    /**
     * @Route("/new", name="appointment_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $appointment = new Appointment();
        $user = $this->security->getUser();
        $appointment->setCompany($user->getCompany());
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('appointment_index');
        }

        return $this->render('appointment/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="appointment_show", methods={"GET"})
     * @param AppointmentRepository $appointmentRepository
     * @param $id
     * @return Response
     * @throws NonUniqueResultException
     */
    public function show(AppointmentRepository $appointmentRepository,$id): Response
    {
        $user = $this->security->getUser();
        $appointment = $appointmentRepository->findOneByCompanyID($user->getCompany(), $id);
        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="appointment_edit", methods={"GET","POST"})
     * @param Request $request
     * @param AppointmentRepository $appointmentRepository
     * @param $id
     * @return Response
     * @throws NonUniqueResultException
     */
    public function edit(Request $request, AppointmentRepository $appointmentRepository, $id): Response
    {
        $user = $this->security->getUser();
        $appointment = $appointmentRepository->findOneByCompanyID($user->getCompany(), $id);
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('appointment_index');
        }

        return $this->render('appointment/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="appointment_delete", methods={"DELETE"})
     * @param Request $request
     * @param Appointment $appointment
     * @return Response
     */
    public function delete(Request $request, Appointment $appointment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('appointment_index');
    }

    public function getAppointmentActions(Appointment $appointment){
        return $this->view($appointment,Response::HTTP_OK);
    }

    public function getAppointmentsActions(){
        $data = $this->appointmentRepository->findAll();
        return $this->view($data, Response::HTTP_OK);
    }

    public function getAppointmentPatientActions(Patient $patient){
        return $this->view($patient,Response::HTTP_OK);
    }

    /**
     * @Rest\RequestParam(name="title", description="Title", nullable=false)
     * @Rest\RequestParam(name="beginAt", description="beginAt", nullable=false)
     * @Rest\RequestParam(name="endAt", description="endAt", nullable=false)
     * @Rest\RequestParam(name="treatmentId", description="treatmentId", nullable=false)
     * @Rest\RequestParam(name="patientId", description="patientId", nullable=false)
     * @Rest\RequestParam(name="color", description="color", nullable=true)
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function postAppointmentAction(ParamFetcher $paramFetcher){
        $title = $paramFetcher->get('title');
        $beginAt = $paramFetcher->get('beginAt');
        $endAt = $paramFetcher->get('endAt');

        $treatmentId = $paramFetcher->get('treatmentId');
        $treatment = $this->treatmentRepository->findOneBy(['id' => $treatmentId]);

        $patientId = $paramFetcher->get('patientId');
        $patient = $this->patientRepository->findOneBy(['id' => $patientId]);

        $color = $paramFetcher->get('color');

        if($patientId){
            $appointment = new Appointment();
            $appointment->setTitle($title);
            $appointment->setBeginAt($beginAt);
            $appointment->setEndAt($endAt);
            $appointment->setTreatment($treatment);
            $appointment->setPatient($patient);
            $appointment->setColor($color);

            $this->entityManager->persist($appointment);
            $this->entityManager->flush();
            return $this->view($appointment,Response::HTTP_CREATED);

        }
        return $this->view(['title' => 'this cannot be null'],Response::HTTP_BAD_REQUEST);
    }
}
