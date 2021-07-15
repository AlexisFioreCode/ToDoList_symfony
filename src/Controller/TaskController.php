<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{

    #[Route('/task/new/{projectId}', name: 'task_new', methods: ['GET', 'POST'])]
    public function new(int $projectId , Request $request, ProjectRepository $projectRepository): Response
    {
        
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project = $projectRepository->find($projectId);
            $task->setProject($project);
            $task->setCreatedDate(new \DateTime());
            $task->setState("En cours");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('project_show', ['id'=> $project->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }
    
    #[Route('/task/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_show', ['id'=> $task->getProject()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/task/delete/{!id<\d+>?1}', name: 'task_delete')]
    public function delete(int $id=1 , TaskRepository $taskRepository ): Response
    {    

        $task = $taskRepository->find($id);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('project_show', ['id'=> $task->getProject()->getId()], Response::HTTP_SEE_OTHER);
    }
}
