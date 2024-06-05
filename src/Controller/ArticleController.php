<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Article;
use App\Form\ArticleType;

use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }
    #[Route('/', name: 'article_new', methods: ['GET', 'POST'])]
    // #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setCreatedAt(new \DateTimeImmutable());
            $article->setUsers($this->getUser());
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/articles', name: 'user_articles')]
    public function userArticles(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si un utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette ressource.');
        }

        // Récupérer tous les articles de l'utilisateur connecté
        $articles = $entityManager->getRepository(Article::class)->findBy(['users' => $user]);

        // Render the template with the articles
        return $this->render('article/user_articles.html.twig', [
            'articles' => $articles,
        ]);
    }

  /*  public function edit(EntityManagerInterface $entityManager,Request $request, $id)
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $form = $this->createForm(ArticleType::class, $article);

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }*/
    public function edit(EntityManagerInterface $entityManager, Request $request, $id)
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('Article not found');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications dans la base de données
            $entityManager->flush();

            // Rediriger vers une autre page (par exemple, la liste des articles)
            return $this->redirectToRoute('user_articles'); // Assurez-vous que 'article_list' est la bonne route pour la liste des articles
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete')]
    public function deleteAction(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé avec cet id');
        }

        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success', 'L\'article a été supprimé avec succès.');

        return $this->redirectToRoute('user_articles');
    }

    #[Route('/article/{id}', name:'article_detail')]
    public function detailAction(Request $request, EntityManagerInterface $entityManager, $id)
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé avec cet id');
        }

        return $this->render('article/detail.html.twig', [
            'article' => $article,
        ]);
    }


}
