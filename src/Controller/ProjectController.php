<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use App\Service\ProjectManager;

class ProjectController extends AbstractController {

    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/projects", methods={"GET"})
     */
    public function getPosts(): Response
    {
        $projectRepository = $this->getDoctrine()->getRepository(Project::class);
        $projects = $projectRepository->findAll();

        $jsonContent = $this->serializer->serialize($projects, 'json');
        // , ['groups' => 'project']

        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/projects", name="add_project", methods={"POST"})
     */
    public function addProject(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $project = new Project();
        if (isset($data['name'])) {
            $project->setName($data['name']);
        }
        if (isset($data['slug'])) {
            $project->setSlug($data['slug']);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($project);
        $entityManager->flush();

        $jsonContent = $this->serializer->serialize($project, 'json');
        return new JsonResponse(['status' => 'Project created', 'project' => $jsonContent], Response::HTTP_CREATED);
    }

    /**
     * @Route("/projects/{id}", name="delete_page", methods={"DELETE"})
     */
    public function deletePage(int $id, ProjectRepository $projectRepository): JsonResponse
    {
        $project = $projectRepository->find($id);

        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($project);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Project deleted successfully'], Response::HTTP_OK);
    }
}
