<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('main/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('main/contact-us.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('main/faq.html.twig');
    }

    #[Route('/pricing', name: 'app_pricing')]
    public function pricing(): Response
    {
        return $this->render('main/pricing.html.twig');
    }

    #[Route('/become-instructor', name: 'app_become_instructor')]
    public function becomeInstructor(): Response
    {
        return $this->render('main/become-instructor.html.twig');
    }

    #[Route('/coming-soon', name: 'app_coming_soon')]
    public function comingSoon(): Response
    {
        return $this->render('main/coming-soon.html.twig');
    }

    #[Route('/error-404', name: 'app_error_404')]
    public function error404(): Response
    {
        return $this->render('main/error-404.html.twig');
    }

    #[Route('/request-demo', name: 'app_request_demo')]
    public function requestDemo(): Response
    {
        return $this->render('main/request-demo.html.twig');
    }

    #[Route('/request-access', name: 'app_request_access')]
    public function requestAccess(): Response
    {
        return $this->render('main/request-access.html.twig');
    }

    #[Route('/abroad-single', name: 'app_abroad_single')]
    public function abroadSingle(): Response
    {
        return $this->render('main/abroad-single.html.twig');
    }

    #[Route('/university-admission', name: 'app_university_admission')]
    public function universityAdmission(): Response
    {
        return $this->render('main/university-admission-form.html.twig');
    }

    #[Route('/book-class', name: 'app_book_class')]
    public function bookClass(): Response
    {
        return $this->render('main/book-class.html.twig');
    }

    // Index pages with variants
    #[Route('/index-2', name: 'app_index_2')]
    public function index2(): Response
    {
        return $this->render('main/index-2.html.twig');
    }

    #[Route('/index-3', name: 'app_index_3')]
    public function index3(): Response
    {
        return $this->render('main/index-3.html.twig');
    }

    #[Route('/index-4', name: 'app_index_4')]
    public function index4(): Response
    {
        return $this->render('main/index-4.html.twig');
    }

    #[Route('/index-5', name: 'app_index_5')]
    public function index5(): Response
    {
        return $this->render('main/index-5.html.twig');
    }

    #[Route('/index-6', name: 'app_index_6')]
    public function index6(): Response
    {
        return $this->render('main/index-6.html.twig');
    }

    #[Route('/index-7', name: 'app_index_7')]
    public function index7(): Response
    {
        return $this->render('main/index-7.html.twig');
    }

    #[Route('/index-8', name: 'app_index_8')]
    public function index8(): Response
    {
        return $this->render('main/index-8.html.twig');
    }

    #[Route('/index-9', name: 'app_index_9')]
    public function index9(): Response
    {
        return $this->render('main/index-9.html.twig');
    }

    #[Route('/index-10', name: 'app_index_10')]
    public function index10(): Response
    {
        return $this->render('main/index-10.html.twig');
    }

    #[Route('/index-11', name: 'app_index_11')]
    public function index11(): Response
    {
        return $this->render('main/index-11.html.twig');
    }

    #[Route('/index-12', name: 'app_index_12')]
    public function index12(): Response
    {
        return $this->render('main/index-12.html.twig');
    }
}
