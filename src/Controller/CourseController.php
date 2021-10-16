<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Exam;
use App\Repository\CourseRepository;
use App\Repository\ExamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CourseController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ExamRepository $examRepository,
        private CourseRepository $courseRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @Route("/courses", methods={"GET"})
     */
    public function index(): JsonResponse
    {
        $courses = $this->courseRepository->findAll();
        $courses = $this->serializer->serialize($courses, 'json');

        return JsonResponse::fromJsonString($courses, Response::HTTP_OK);
    }

    /**
     * @Route("/courses/create", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $course = new Course();
        $course->setName($data['name']);

        $exam = $this->examRepository->findByName($data['exam']);

        if (!$exam) {
            return new JsonResponse(['No exam found.'], Response::HTTP_NOT_FOUND);
        }

        $course->setExam($exam);
        $course->setFeatured($data['featured']);

        $errors = $validator->validate($course);
        if (count($errors) > 0) {
            $errors = $this->serializer->serialize($errors, 'json');
            return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($course);
        $this->entityManager->flush();

        $course = $this->serializer->serialize($course, 'json');

        return JsonResponse::fromJsonString($course, Response::HTTP_CREATED);
    }

    /**
     * @Route("/courses/{id}", methods={"GET"})
     */
    public function show(int $id): JsonResponse
    {
        $course = $this->courseRepository->find($id);

        if (!$course) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $course = $this->serializer->serialize($course, 'json');

        return JsonResponse::fromJsonString($course, Response::HTTP_OK);
    }

    /**
     * @Route("/courses/{id}", methods={"PUT", "PATCH"})
     */
    public function edit(int $id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $course = $this->courseRepository->find($id);

        if (null === $course) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $course->setName($data['name']);

        $exam = $this->examRepository->findByName($data['exam']);

        if (!$exam) {
            return new JsonResponse(['No exam found.'], Response::HTTP_NOT_FOUND);
        }

        $course->setExam($exam);
        $course->setFeatured($data['featured']);

        $errors = $validator->validate($course);
        if (count($errors) > 0) {
            $errors = $this->serializer->serialize($errors, 'json');
            return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $course = $this->serializer->serialize($course, 'json');

        return JsonResponse::fromJsonString($course, Response::HTTP_OK);
    }

    /**
     * @Route("/courses/{id}", methods={"DELETE"})
     */
    public function delete(int $id): JsonResponse
    {
        $course = $this->courseRepository->find($id);

        if (null === $course) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($course);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
