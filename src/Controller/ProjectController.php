<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

    /**
    * @IsGranted("IS_AUTHENTICATED_FULLY")
    */

class ProjectController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    #[Route('/project', name: 'project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {   
        $projectRepository=$this->getDoctrine()->getRepository(Project::class);
        $projects = $projectRepository->getProjects($this->getUser()->getId());


        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->setCreatedDate(new \DateTime());
            $project->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        $task = $project->getTasks();
        return $this->render('project/show.html.twig', [
            'project' => $project,
            'tasks' => $task,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/project/delete/{!id<\d+>?1}', name: 'project_delete')]
    public function delete(int $id=1 , ProjectRepository $projectRepository ): Response
    {
        $project = $projectRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($project);
        $entityManager->flush();
       
        return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
    }
}
