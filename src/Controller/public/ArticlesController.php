<?php

namespace App\Controller\public;

use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticlesController extends AbstractController
{
  #[Route(path: '/articles', name: 'articles_list', methods: ['GET'])]
  public function articlesPublishedList(ArticleRepository $articleRepository): Response {
    $articles = $articleRepository->findBy(['status' => "published"]);
    return $this->render('public/articles/list.html.twig', ['articles'=>$articles]);
  }

  #[Route(path: '/article/{id}/show', name: 'article_show', requirements: ['id'=>'\d+'], methods: ['GET'])]
  public function articlePublishedShow(int $id, ArticleRepository $articleRepository): Response {
    $article = $articleRepository->find($id);
    if (!$article){
      $this->addFlash("error", "Article inexistant");
      return $this->redirectToRoute('articles_list');
    }
    @$allComments = $article->getComments();
    $commentsPublished = [];
    foreach ($allComments as $comment) {
      if($comment->getStatus() === "published") {
        $commentsPublished[] = $comment;
      }
    }

    $articleStatus = $article->getStatus();

    if($articleStatus !== "published" || !$article) {
      $this->addFlash('error', "Cet article n'existe pas.");
      return $this->redirectToRoute('articles_list');
    }
    return $this->render('public/articles/show.html.twig', ['article'=>$article, "comments"=>$commentsPublished]);
  }
}