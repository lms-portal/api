<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Repository\ExamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExamController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ExamRepository $examRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @Route("/exams", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $exams = $this->examRepository->findAll();
        $exams = $this->serializer->serialize($exams, 'json');

        return JsonResponse::fromJsonString($exams, Response::HTTP_OK);
    }

    /**
     * @Route("/exams/create", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $exam = new Exam();
        $exam->setName($data['name']);

        $errors = $validator->validate($exam);
        if (count($errors) > 0) {
            $errors = $this->serializer->serialize($errors, 'json');
            return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($exam);
        $this->entityManager->flush();

        $exam = $this->serializer->serialize($exam, 'json');

        return JsonResponse::fromJsonString($exam, Response::HTTP_CREATED);
    }

    /**
     * @Route("/exams/{id}", methods={"GET"})
     */
    public function show(int $id): JsonResponse
    {
        $exam = $this->examRepository->find($id);

        if (!$exam) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $exam = $this->serializer->serialize($exam, 'json');

        return JsonResponse::fromJsonString($exam, Response::HTTP_OK);
    }

    /**
     * @Route("/exams/{id}", methods={"PUT", "PATCH"})
     */
    public function edit(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $exam = $this->examRepository->find($id);

        if (null === $exam) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $exam->setName($data['name']);

        $errors = $validator->validate($exam);
        if (count($errors) > 0) {
            $errors = $this->serializer->serialize($errors, 'json');
            return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $exam = $this->serializer->serialize($exam, 'json');

        return JsonResponse::fromJsonString($exam, Response::HTTP_OK);
    }

    /**
     * @Route("/exams/{id}", methods={"DELETE"})
     */
    public function delete(int $id): JsonResponse
    {
        $exam = $this->examRepository->find($id);

        if (null === $exam) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($exam);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
