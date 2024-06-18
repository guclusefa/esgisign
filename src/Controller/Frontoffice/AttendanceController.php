<?php

namespace App\Controller\Frontoffice;

use App\Entity\Attendance;
use App\Form\AttendanceType;
use App\Repository\AttendanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/frontoffice/attendance')]
class AttendanceController extends AbstractController
{
    #[Route('/', name: 'app_frontoffice_attendance_index', methods: ['GET'])]
    public function index(AttendanceRepository $attendanceRepository): Response
    {
        return $this->render('frontoffice/attendance/index.html.twig', [
            'attendances' => $attendanceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_frontoffice_attendance_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $attendance = new Attendance();
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($attendance);
            $entityManager->flush();

            return $this->redirectToRoute('app_frontoffice_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontoffice/attendance/new.html.twig', [
            'attendance' => $attendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_frontoffice_attendance_show', methods: ['GET'])]
    public function show(Attendance $attendance): Response
    {
        return $this->render('frontoffice/attendance/show.html.twig', [
            'attendance' => $attendance,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_frontoffice_attendance_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AttendanceType::class, $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_frontoffice_attendance_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('frontoffice/attendance/edit.html.twig', [
            'attendance' => $attendance,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_frontoffice_attendance_delete', methods: ['POST'])]
    public function delete(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$attendance->getId(), $request->request->get('_token'))) {
            $entityManager->remove($attendance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_frontoffice_attendance_index', [], Response::HTTP_SEE_OTHER);
    }
}
